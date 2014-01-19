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
class ComposerInstalledStep extends AbstractStep implements StepInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute(ConsumerEvent $event, $directory)
    {
        $output = null;
        $workingDirectory = $this->workingTempPath.'/'.$directory;

        $process = $this->runProcess(sprintf('%s show --installed', $this->composerBinPath), $workingDirectory, $output);
        if ($process->isSuccessful()) {
            $this->triggerComposerInstalled($event, array('message' => $process->getOutput()));
        }

        return 0;
    }
}
