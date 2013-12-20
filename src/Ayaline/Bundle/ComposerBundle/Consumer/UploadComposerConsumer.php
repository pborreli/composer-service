<?php

namespace Ayaline\Bundle\ComposerBundle\Consumer;

use Sonata\NotificationBundle\Consumer\ConsumerEvent;
use Sonata\NotificationBundle\Consumer\ConsumerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Sonata\NotificationBundle\Exception\InvalidParameterException;
use Symfony\Component\Process\Process;

class UploadComposerConsumer implements ConsumerInterface
{
    /**
     */
    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function process(ConsumerEvent $event)
    {
        $message = $event->getMessage();

        $fs = new Filesystem();
        $path = $message->getValue('path');
        echo $path;
        $fs->mkdir($path);
        $fs->copy('/Users/ayoub/tmp/composer.json', $path.'/composer.json');
        $process = new Process('php /Users/ayoub/tmp/composer.phar update -q --no-scripts --prefer-dist');
        $process->setWorkingDirectory($path);
        $process->run();

        if (!$process->isSuccessful()) {
            echo $process->getErrorOutput();
            //throw new \RuntimeException($process->getErrorOutput());
        }

        $process = new Process('zip -rq vendor.zip vendor/');
        $process->setWorkingDirectory($path);
        $process->run();

        print $process->getOutput();

    }
}