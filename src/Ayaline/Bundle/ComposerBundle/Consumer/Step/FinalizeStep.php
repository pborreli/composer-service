<?php

namespace Ayaline\Bundle\ComposerBundle\Consumer\Step;

use Sonata\NotificationBundle\Consumer\ConsumerEvent;
use Symfony\Component\Filesystem\Filesystem;

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

        $this->pusher->trigger($this->getChannel($event), 'consumer:success', array('link' => '/assets/'.$sha1LockFile.'/vendor.zip'));
        $this->filesystem->remove($this->workingTempPath.'/'.$directory);

        return 0;
    }
}
