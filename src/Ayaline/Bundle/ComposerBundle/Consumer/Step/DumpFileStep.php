<?php

namespace Ayaline\Bundle\ComposerBundle\Consumer\Step;

use Sonata\NotificationBundle\Consumer\ConsumerEvent;
use Symfony\Component\Filesystem\Filesystem;

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
        $this->pusher->trigger($this->getChannel($event), 'consumer:new-step', array('message' => 'Starting async job'));

        $this->filesystem->mkdir($this->workingTempPath.'/'.$directory);
        $this->filesystem->dumpFile($this->workingTempPath.'/'.$directory.'/composer.json', $event->getMessage()->getValue('body'));

        return 0;
    }
}
