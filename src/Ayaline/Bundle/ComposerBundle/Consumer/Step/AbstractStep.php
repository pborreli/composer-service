<?php

namespace Ayaline\Bundle\ComposerBundle\Consumer\Step;

use Symfony\Component\Filesystem\Filesystem;
use Sonata\NotificationBundle\Consumer\ConsumerEvent;

/**
 * @author Hubert Moutot <hubert.moutot@gmail.com>
 */
abstract class AbstractStep implements StepInterface
{
    /**
     * @var \Pusher
     */
    protected $pusher;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $rootDir;

    /**
     * @var string
     */
    protected $workingTempPath;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
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

    /**
     * Triggers a "consumer:new-step" message on Pusher.
     *
     * @param ConsumerEvent $event
     * @param array $message
     */
    protected function triggerNewStep(ConsumerEvent $event, $message)
    {
        $this->pusher->trigger($this->getChannel($event), 'consumer:new-step', $message);
    }

    /**
     * Triggers a "consumer:step-error" message on Pusher.
     *
     * @param ConsumerEvent $event
     * @param array $message
     */
    protected function triggerStepError(ConsumerEvent $event, $message)
    {
        $this->pusher->trigger($this->getChannel($event), 'consumer:step-error', $message);
    }

    /**
     * Triggers a "consumer:error" message on Pusher.
     *
     * @param ConsumerEvent $event
     * @param array $message
     */
    protected function triggerError(ConsumerEvent $event, $message)
    {
        $this->pusher->trigger($this->getChannel($event), 'consumer:error', $message);
    }

    /**
     * Triggers a "consumer:success" message on Pusher.
     *
     * @param ConsumerEvent $event
     * @param array $message
     */
    protected function triggerSuccess(ConsumerEvent $event, $message)
    {
        $this->pusher->trigger($this->getChannel($event), 'consumer:success', $message);
    }
}
