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

/**
 * @author Hubert Moutot <hubert.moutot@gmail.com>
 */
class DumpFileStep extends AbstractStep implements StepInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute(ConsumerEvent $event, $directory)
    {
        $this->triggerNewStep($event, array('message' => 'Starting async job'));

        $this->filesystem->mkdir($this->workingTempPath.'/'.$directory);
        $this->filesystem->dumpFile(
            sprintf('%s/%s/composer.json', $this->workingTempPath, $directory),
            $event->getMessage()->getValue('body')
        );

        return 0;
    }
}
