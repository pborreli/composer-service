<?php

namespace Ayaline\Bundle\ComposerBundle\Consumer\Step;

use Sonata\NotificationBundle\Consumer\ConsumerEvent;

/**
 * @author Hubert Moutot <hubert.moutot@gmail.com>
 */
class FinalizeStep extends AbstractStep implements StepInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute(ConsumerEvent $event, $directory)
    {
        $sha1LockFile = sha1_file($this->workingTempPath.'/'.$directory.'/composer.lock');

        $this->triggerSuccess($event, array('link' => '/assets/'.$sha1LockFile.'/vendor.zip'));
        $this->filesystem->remove($this->workingTempPath.'/'.$directory);

        return 0;
    }
}
