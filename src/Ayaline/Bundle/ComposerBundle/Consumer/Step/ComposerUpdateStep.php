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
        $this->triggerNewStep($event, array('message' => './composer update'));

        $output = null;
        $workingDirectory = $this->workingTempPath.'/'.$directory;

        $hasDevDeps = $event->getMessage()->getValue('hasDevDependencies');
        $requireDevOption = true === $hasDevDeps ? '--dev' : '--no-dev';

        $commandLine = sprintf('%s update %s', $this->composerBinPath, $requireDevOption);
        $commandLine .= ' --no-scripts --prefer-dist --no-progress';

        $process = $this->runProcess('hhvm '.$commandLine, $workingDirectory, $output);

        if (!$process->isSuccessful()
            || false !== strpos($output, 'Your requirements could not be resolved to an installable set of packages.')
            || false !== strpos($output, 'HipHop Fatal error')) {

            $this->triggerNewStep($event, array('message' => 'Restarting...'));

            $output = null;
            $process = $this->runProcess($commandLine, $workingDirectory, $output);
        }

        if (!$process->isSuccessful()) {
            $this->triggerError($event, array('message' => nl2br($output)));
            $this->triggerStepError($event, array('message' => 'Composer failed'));

            return 1;
        }

        if (!is_dir($this->workingTempPath.'/'.$directory.'/vendor')
            || !is_file($this->workingTempPath.'/'.$directory.'/composer.lock')) {
            $this->triggerStepError($event, array('message' => 'Fatal error during composer update'));

            return 1;
        }

        $this->triggerComposerOutput($event, array('message' => $process->getOutput()));

        $output = null;
        $process = $this->runProcess(sprintf('%s show --installed', $this->composerBinPath), $workingDirectory, $output);
        if ($process->isSuccessful()) {
            $this->triggerComposerInstalled($event, array('message' => $process->getOutput()));
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
