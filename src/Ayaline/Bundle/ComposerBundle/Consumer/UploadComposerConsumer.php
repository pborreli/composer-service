<?php

namespace Ayaline\Bundle\ComposerBundle\Consumer;

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

        $path = '/dev/shm/composer/'. uniqid('composer', true);

        $fs->mkdir($path);
        $fs->dumpFile($path.'/composer.json', $body);

        $pusher->trigger($channelName, 'notice', array('msg' => 'Launching composer update'));

        $process = new Process('hhvm /usr/local/bin/composer update -q --no-scripts --prefer-dist');
        $process->setWorkingDirectory($path);
        $process->run();

        if (!$process->isSuccessful()) {
            $pusher->trigger($channelName, 'error', array('message' => $process->getErrorOutput()));
        }

        $pusher->trigger($channelName, 'notice', array('msg' => 'Compressing vendor.zip'));

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

        print $process->getOutput();

    }
}