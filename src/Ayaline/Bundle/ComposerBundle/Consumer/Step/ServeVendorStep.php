<?php

namespace Ayaline\Bundle\ComposerBundle\Consumer\Step;

use Sonata\NotificationBundle\Consumer\ConsumerEvent;
use Symfony\Component\Filesystem\Filesystem;
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
            $this->pusher->trigger($this->getChannel($event), 'consumer:new-step', array('message' => 'Serving cached vendor.zip'));

            return 0;
        }

        $this->pusher->trigger($this->getChannel($event), 'consumer:new-step', array('message' => 'Compressing vendor.zip'));

        $this->filesystem->mkdir($resultPath);

        $process = new Process('zip -rq '.$resultPath.'/vendor.zip .');
        $process->setWorkingDirectory($this->workingTempPath.'/'.$directory);
        $process->run();

        if (!$process->isSuccessful()) {
            $this->pusher->trigger($this->getChannel($event), 'consumer:error', array('message' => $process->getErrorOutput()));

            return 1;
        }

        return 0;
    }
}
