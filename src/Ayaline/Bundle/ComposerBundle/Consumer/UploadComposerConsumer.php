<?php

namespace Ayaline\Bundle\ComposerBundle\Consumer;

use SensioLabs\Security\SecurityChecker;
use Sonata\NotificationBundle\Consumer\ConsumerEvent;
use Sonata\NotificationBundle\Consumer\ConsumerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class UploadComposerConsumer implements ConsumerInterface
{
    /**
     * @var \Pusher
     */
    public $pusher;

    /**
     * @var string
     */
    public $root_dir;

    /**
     * @var string
     */
    public $working_temp_path;

    /**
     * @var string
     */
    public $composer_bin_path;


    /**
     * Constructor
     *
     * @param \Pusher $pusher
     *
     */
    public function __construct($root_dir, $working_temp_path = '/dev/shm/composer/', $composer_bin_path = '/usr/local/bin/composer')
    {
        $this->root_dir = $root_dir;
        $this->working_temp_path = $working_temp_path;
        $this->composer_bin_path = $composer_bin_path;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ConsumerEvent $event)
    {
        $message = $event->getMessage();
        $body = $message->getValue('body');
        $channelName = $message->getValue('channelName');

        $this->pusher->trigger($channelName, 'consumer:new-step', array('message' => 'Starting async job'));

        $path = $this->working_temp_path;
        $path = rtrim($path, '/').'/';
        $path = $path.uniqid('composer', true);

        $composerBinPath = $this->composer_bin_path;

        $fs = new Filesystem();
        $fs->mkdir($path);
        $fs->dumpFile($path.'/composer.json', $body);

        $this->pusher->trigger($channelName, 'consumer:new-step', array('message' => './composer update'));

        $process = new Process("hhvm $composerBinPath update --no-scripts --prefer-dist --no-progress --no-dev");
        $process->setWorkingDirectory($path);
        $process->setTimeout(300);

        $callback = function ($type, $data) use (&$output) {
            if ('' == $data = trim($data)) {
                return;
            }
            if ($type == 'err') {
                $output .= $data.PHP_EOL;
            } else {
                $output = $data.PHP_EOL;
            }
        };

        $output = null;
        try {
            $process->run($callback);
        }catch (\Exception $e) {
            $this->pusher->trigger($channelName, 'consumer:step-error', array('message' => 'HHVM composer failed'));
        }

        $requirements = 'Your requirements could not be resolved to an installable set of packages.';

        if (!$process->isSuccessful()
            || false !== strpos($output, $requirements)
            || false !== strpos($output, 'HipHop Fatal error')) {

            $this->pusher->trigger($channelName, 'consumer:new-step', array('message' => 'Restarting ...'));

            $process = new Process("$composerBinPath update --no-scripts --prefer-dist --no-progress --no-dev");
            $process->setWorkingDirectory($path);
            $process->setTimeout(300);
            $output = null;
            $process->run($callback);
        }

        if (!$process->isSuccessful()) {
            $this->pusher->trigger($channelName, 'consumer:error', array('message' => nl2br($output)));
            $this->pusher->trigger($channelName, 'consumer:step-error', array('message' => 'Composer failed'));
            return 1;
        }

        if (!is_dir($path.'/vendor') || !is_file($path.'/composer.lock')) {
            $this->pusher->trigger($channelName, 'consumer:step-error', array('message' => 'Fatal error during composer update'));
            return 1;
        }

        $this->pusher->trigger($channelName, 'consumer:new-step', array('message' => 'Checking vulnerability'));
        $checker = new SecurityChecker();

        $alerts = $checker->check($path.'/composer.lock', 'json');
        try {
            $alerts = $checker->check($path.'/composer.lock', 'json');
        }catch(\RuntimeException $e){
            $this->pusher->trigger($channelName, 'consumer:error', array('message' => $e->getMessage(), 'more' => $alerts));
        } catch(\RuntimeException $e) {
            $pusher->trigger($channelName, 'consumer:error', array('message' => $e->getMessage(), 'more' => $alerts));
            return 1;
        }

        $vulnerabilityCount = $checker->getLastVulnerabilityCount();
        if ($vulnerabilityCount > 0) {
            $this->pusher->trigger($channelName, 'consumer:step-error', array('message' => 'Vulnerability found : '.$vulnerabilityCount));
        }

        $sha1LockFile = sha1_file($path.'/composer.lock');

        $resultPath = $this->root_dir.'/../web/assets/'.$sha1LockFile;

        if (is_file($resultPath.'/vendor.zip')) {
            $this->pusher->trigger($channelName, 'consumer:new-step', array('message' => 'Serving cached vendor.zip'));
        } else {
            $this->pusher->trigger($channelName, 'consumer:new-step', array('message' => 'Compressing vendor.zip'));

            $fs->mkdir($resultPath);
            $process = new Process('zip -rq '.$resultPath.'/vendor.zip .');
            $process->setWorkingDirectory($path);
            $process->run();

            if (!$process->isSuccessful()) {
                $this->pusher->trigger($channelName, 'consumer:error', array('message' => $process->getErrorOutput()));
            }
        }

        $this->pusher->trigger($channelName, 'consumer:success', array('link' => '/assets/'.$sha1LockFile.'/vendor.zip'));
        $fs->remove($path);

        return 0;
    }
}