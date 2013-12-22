<?php

namespace Ayaline\Bundle\ComposerBundle\Consumer;

use Composer\Json\JsonFile;
use Composer\Json\JsonValidationException;
use Sonata\NotificationBundle\Consumer\ConsumerEvent;
use Sonata\NotificationBundle\Consumer\ConsumerInterface;
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

        $composers_tmp_path = $this->container->getParameter('composers_tmp_path', '/dev/shm/composer/');
        $composers_tmp_path = rtrim($composers_tmp_path, '/').'/';

        $path = $composers_tmp_path.uniqid('composer', true);

        $fs->mkdir($path);
        $fs->dumpFile($path.'/composer.json', $body);

        try {
            $jsonFile = new JsonFile($path.'/composer.json');
            $jsonFile->validateSchema(JsonFile::LAX_SCHEMA);
        } catch (\Exception $exception) {
            $from = array($path);
            $to   = array('');
            $message = str_replace($from, $to, $exception->getMessage());
            $pusher->trigger($channelName, 'error', array('message' => nl2br($message)));
            return 1;
        }

        $pusher->trigger($channelName, 'notice', array('message' => 'Updating...'));

        $process = new Process('hhvm /usr/local/bin/composer update --no-scripts --prefer-dist --no-progress --no-dev');
        $process->setWorkingDirectory($path);
        $process->run();

        $requirements = 'Your requirements could not be resolved to an installable set of packages.';

        if (!$process->isSuccessful() && false !== strpos($process->getOutput(), $requirements)) {

            $pusher->trigger($channelName, 'notice', array('message' => 'Restarting...'));

            $process = new Process('/usr/local/bin/composer update --no-scripts --prefer-dist --no-progress --no-dev');
            $process->setWorkingDirectory($path);
            $process->run();
        }

        if (!$process->isSuccessful()) {
            $pusher->trigger($channelName, 'error', array('message' => $process->getOutput()));
            return 1;
        }

        if (!is_dir($path.'/vendor')) {
            $pusher->trigger($channelName, 'error', array('message' => 'Fatal error during composer update'));
            return 1;
        }

        $pusher->trigger($channelName, 'notice', array('message' => 'Compressing...'));

        $uniqid = uniqid();
        $rootDir = $this->container->get('kernel')->getRootDir();
        $resultPath = $rootDir.'/../web/assets/'.$uniqid;
        $fs->mkdir($resultPath);
        $process = new Process('zip -rq '.$resultPath.'/vendor.zip vendor/');
        $process->setWorkingDirectory($path);
        $process->run();

        if (!$process->isSuccessful()) {
            $pusher->trigger($channelName, 'error', array('message' => $process->getErrorOutput()));
        }

        $pusher->trigger($channelName, 'success', array('link' => '/assets/'.$uniqid.'/vendor.zip'));

        $fs->remove($path);

        return 0;
    }
}