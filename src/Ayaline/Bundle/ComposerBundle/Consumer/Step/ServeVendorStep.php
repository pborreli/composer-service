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
class ServeVendorStep extends AbstractStep implements StepInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute(ConsumerEvent $event, $directory)
    {
        $sha1LockFile = sha1_file($this->workingTempPath.'/'.$directory.'/composer.lock');
        $resultPath = $this->rootDir.'/../web/assets/'.$sha1LockFile;

        if (is_file($resultPath.'/vendor.zip')) {
            $this->triggerNewStep($event, array('message' => 'Serving cached vendor.zip'));

            return 0;
        }

        $this->triggerNewStep($event, array('message' => 'Compressing vendor.zip'));

        $this->filesystem->mkdir($resultPath);

        $process = new Process('zip -rq '.$resultPath.'/vendor.zip .');
        $process->setWorkingDirectory($this->workingTempPath.'/'.$directory);
        $process->run();

        if (!$process->isSuccessful()) {
            $this->triggerError($event, array('message' => $process->getErrorOutput()));

            return 1;
        }

        return 0;
    }
}
