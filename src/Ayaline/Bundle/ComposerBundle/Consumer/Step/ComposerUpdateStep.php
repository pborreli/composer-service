<?php

namespace Ayaline\Bundle\ComposerBundle\Consumer\Step;

use Sonata\NotificationBundle\Consumer\ConsumerEvent;
use Symfony\Component\Process\Process;

/**
 * @author Hubert Moutot <hubert.moutot@gmail.com>
 */
class ComposerUpdateStep extends AbstractStep implements StepInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute(ConsumerEvent $event, $directory)
    {
        $this->pusher->trigger($this->getChannel($event), 'consumer:new-step', array('message' => './composer update'));

        $output = null;
        $workingDirectory = $this->workingTempPath.'/'.$directory;

        $hasDevDeps = $event->getMessage()->getValue('hasDevDependencies');
        $requireDevOption = true === $hasDevDeps ? '--dev' : '--no-dev';

        try {
            $process = $this->runProcess(
                sprintf(
                    "hhvm %s update %s --no-scripts --prefer-dist --no-progress",
                    $this->composerBinPath,
                    $requireDevOption
                ),
                $workingDirectory,
                $output
            );
        } catch (\Exception $e) {
            $this->pusher->trigger($this->getChannel($event), 'consumer:step-error', array('message' => 'HHVM composer failed'));

            return 1;
        }

        $requirements = 'Your requirements could not be resolved to an installable set of packages.';

        if (!$process->isSuccessful()
            || false !== strpos($output, $requirements)
            || false !== strpos($output, 'HipHop Fatal error')) {

            $this->pusher->trigger($this->getChannel($event), 'consumer:new-step', array('message' => 'Restarting ...'));

            $output = null;
            $process = $this->runProcess(
                sprintf(
                    "%s update %s --no-scripts --prefer-dist --no-progress",
                    $this->composerBinPath,
                    $requireDevOption
                ),
                $workingDirectory,
                $output
            );
        }

        if (!$process->isSuccessful()) {
            $this->pusher->trigger($this->getChannel($event), 'consumer:error', array('message' => nl2br($output)));
            $this->pusher->trigger($this->getChannel($event), 'consumer:step-error', array('message' => 'Composer failed'));

            return 1;
        }

        if (!is_dir($this->workingTempPath.'/'.$directory.'/vendor') || !is_file($this->workingTempPath.'/'.$directory.'/composer.lock')) {
            $this->pusher->trigger($this->getChannel($event), 'consumer:step-error', array('message' => 'Fatal error during composer update '.$this->workingTempPath.'/'.$directory));

            return 1;
        }

        return 0;
    }

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
