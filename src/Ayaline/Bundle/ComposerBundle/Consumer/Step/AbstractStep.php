<?php

namespace Ayaline\Bundle\ComposerBundle\Consumer\Step;

use Symfony\Component\Filesystem\Filesystem;
use Sonata\NotificationBundle\Consumer\ConsumerEvent;

/**
 * @author Hubert Moutot <hubert.moutot@gmail.com>
 */
abstract class AbstractStep implements StepInterface
{
    protected $pusher;

    protected $filesystem;

    protected $rootDir;

    protected $workingTempPath;

    protected $path;

    protected $composerBinPath;

    /**
     * @param \Pusher $pusher
     */
    public function setPusher(\Pusher $pusher)
    {
        $this->pusher = $pusher;
    }

    /**
     * @param Filesystem $filesystem
     */
    public function setFilesystem(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @param string $rootDir
     */
    public function setRootDir($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    /**
     * @param string $workingTempPath
     */
    public function setWorkingTempPath($workingTempPath)
    {
        $this->workingTempPath = $workingTempPath;
    }

    /**
     * @param string $composerBinPath
     */
    public function setComposerBinPath($composerBinPath)
    {
        $this->composerBinPath = $composerBinPath;
    }

    /**
     * Extracts a channel name from a ConsumerEvent
     *
     * @param  ConsumerEvent $event
     * @return string
     */
    protected function getChannel(ConsumerEvent $event)
    {
        $message = $event->getMessage();

        return $message->getValue('channelName');
    }
}
