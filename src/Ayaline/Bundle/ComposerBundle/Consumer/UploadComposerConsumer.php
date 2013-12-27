<?php

namespace Ayaline\Bundle\ComposerBundle\Consumer;

use Composer\Json\JsonFile;
use Composer\Json\JsonValidationException;
use SensioLabs\Security\SecurityChecker;
use Sonata\NotificationBundle\Consumer\ConsumerEvent;
use Sonata\NotificationBundle\Consumer\ConsumerInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class UploadComposerConsumer implements ConsumerInterface
{
    /**
     * @var $container
     */
    public $container;

    /**
     * Constructor
     *
     * @param ContainerInterface  $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container  = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ConsumerEvent $event)
    {
        $pusher = $this->container->get('lopi_pusher.pusher');
        $fs = new Filesystem();
        $message = $event->getMessage();
        $body = $message->getValue('body');
        $channelName = $message->getValue('channelName');

        $pusher->trigger($channelName, 'consumer:new-step', array('message' => 'Starting async job'));

        $composers_tmp_path = $this->container->getParameter('composers_tmp_path', '/dev/shm/composer/');
        $composers_tmp_path = rtrim($composers_tmp_path, '/').'/';

        $path = $composers_tmp_path.uniqid('composer', true);

        $fs->mkdir($path);
        $fs->dumpFile($path.'/composer.json', $body);

        $pusher->trigger($channelName, 'consumer:new-step', array('message' => 'Validating composer.json'));

        try {
            $jsonFile = new JsonFile($path.'/composer.json');
            $jsonFile->validateSchema(JsonFile::LAX_SCHEMA);
        } catch (\Exception $exception) {
            $from = array($path);
            $to   = array('');
            $message = str_replace($from, $to, $exception->getMessage());
            $pusher->trigger($channelName, 'consumer:error', array('message' => nl2br($message)));
            return 1;
        }

        $pusher->trigger($channelName, 'consumer:new-step', array('message' => './composer update'));

        $process = new Process('hhvm /usr/local/bin/composer update --no-scripts --prefer-dist --no-progress --no-dev');
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
            $pusher->trigger($channelName, 'consumer:step-error', array('message' => 'HHVM composer failed'));
        }

        $requirements = 'Your requirements could not be resolved to an installable set of packages.';

        if (!$process->isSuccessful() || false !== strpos($output, $requirements) || false !== strpos($output, 'HipHop Fatal error')) {

            $pusher->trigger($channelName, 'consumer:new-step', array('message' => 'Restarting ...'));

            $process = new Process('/usr/local/bin/composer update --no-scripts --prefer-dist --no-progress --no-dev');
            $process->setWorkingDirectory($path);
            $process->setTimeout(300);
            $output = null;
            $process->run($callback);
        }

        if (!$process->isSuccessful()) {
            $pusher->trigger($channelName, 'consumer:error', array('message' => nl2br($output)));
            $pusher->trigger($channelName, 'consumer:step-error', array('message' => 'Composer failed'));
            return 1;
        }

        if (!is_dir($path.'/vendor') || !is_file($path.'/composer.lock')) {
            $pusher->trigger($channelName, 'consumer:step-error', array('message' => 'Fatal error during composer update'));
            return 1;
        }

        $pusher->trigger($channelName, 'consumer:new-step', array('message' => 'Checking vulnerability'));
        $checker = new SecurityChecker();

        try {
            $alerts = $checker->check($path.'/composer.lock', 'json');
        }catch(\RuntimeException $e){
            $pusher->trigger($channelName, 'consumer:error', array('message' => $e->getMessage()));
            return 1;
        }

        $vulnerabilityCount = $checker->getLastVulnerabilityCount();
        if ($vulnerabilityCount > 0) {
            $pusher->trigger($channelName, 'consumer:step-error', array('message' => 'Vulnerability found : '.$vulnerabilityCount));
        }

        $sha1LockFile = sha1_file($path.'/composer.lock');

        $rootDir = $this->container->get('kernel')->getRootDir();
        $resultPath = $rootDir.'/../web/assets/'.$sha1LockFile;

        if (is_file($resultPath.'/vendor.zip')) {
            $pusher->trigger($channelName, 'consumer:new-step', array('message' => 'Serving cached vendor.zip'));
        } else {
            $pusher->trigger($channelName, 'consumer:new-step', array('message' => 'Compressing vendor.zip'));

            $fs->mkdir($resultPath);
            $process = new Process('zip -rq '.$resultPath.'/vendor.zip .');
            $process->setWorkingDirectory($path);
            $process->run();

            if (!$process->isSuccessful()) {
                $pusher->trigger($channelName, 'consumer:error', array('message' => $process->getErrorOutput()));
            }
        }
        $pusher->trigger($channelName, 'consumer:success', array('link' => '/assets/'.$sha1LockFile.'/vendor.zip'));

        $fs->remove($path);

        return 0;
    }
}