<?php

/*
 * This file is part of `Composer as a service`.
 *
 * (c) Pascal Borreli <pascal@borreli.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ayaline\Bundle\ComposerBundle\Consumer\Step;

use Symfony\Component\Filesystem\Filesystem;
use Sonata\NotificationBundle\Consumer\ConsumerEvent;
use Symfony\Component\Process\Process;

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
     * @param array         $message
     */
    protected function triggerNewStep(ConsumerEvent $event, $message)
    {
        $this->pusher->trigger($this->getChannel($event), 'consumer:new-step', $message);
    }

    /**
     * Triggers a "consumer:step-error" message on Pusher.
     *
     * @param ConsumerEvent $event
     * @param array         $message
     */
    protected function triggerStepError(ConsumerEvent $event, $message)
    {
        $this->pusher->trigger($this->getChannel($event), 'consumer:step-error', $message);
    }

    /**
     * Triggers a "consumer:error" message on Pusher.
     *
     * @param ConsumerEvent $event
     * @param array         $message
     */
    protected function triggerError(ConsumerEvent $event, $message)
    {
        $this->pusher->trigger($this->getChannel($event), 'consumer:error', $message);
    }

    /**
     * Triggers a "consumer:success" message on Pusher.
     *
     * @param ConsumerEvent $event
     * @param array         $message
     */
    protected function triggerSuccess(ConsumerEvent $event, $message)
    {
        $this->pusher->trigger($this->getChannel($event), 'consumer:success', $message);
    }

    /**
     * Triggers a "consumer:composer-output" message on Pusher.
     *
     * @param ConsumerEvent $event
     * @param array         $message
     */
    protected function triggerComposerOutput(ConsumerEvent $event, $message)
    {
        $this->pusher->trigger($this->getChannel($event), 'consumer:composer-output', $message);
    }

    /**
     * Triggers a "consumer:composer-installed" message on Pusher.
     *
     * @param ConsumerEvent $event
     * @param array         $message
     */
    protected function triggerComposerInstalled(ConsumerEvent $event, $message)
    {
        $this->pusher->trigger($this->getChannel($event), 'consumer:composer-installed', $message);
    }

    /**
     * Triggers a "consumer:vulnerabilities" message on Pusher.
     *
     * @param ConsumerEvent $event
     * @param array         $message
     */
    protected function triggerVulnerabilities(ConsumerEvent $event, $message)
    {
        $this->pusher->trigger($this->getChannel($event), 'consumer:vulnerabilities', $message);
    }

    /**
     * Runs a process
     *
     * @param string $commandline      the command line to execute
     * @param string $workingDirectory the current working directory
     * @param string $output           the output
     *
     * @return Process the resulting Process
     */
    protected function runProcess($commandline, $workingDirectory, &$output)
    {
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

        $process = new Process($commandline);
        $process->setWorkingDirectory($workingDirectory);
        $process->setTimeout(300);
        $process->run($callback);

        return $process;
    }
}
