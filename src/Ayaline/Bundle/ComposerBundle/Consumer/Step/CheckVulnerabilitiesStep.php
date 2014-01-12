<?php

namespace Ayaline\Bundle\ComposerBundle\Consumer\Step;

use Sonata\NotificationBundle\Consumer\ConsumerEvent;
use SensioLabs\Security\SecurityChecker;

/**
 * @author Hubert Moutot <hubert.moutot@gmail.com>
 */
class CheckVulnerabilitiesStep extends AbstractStep implements StepInterface
{
    protected $securityChecker;

    /**
     * Constructor
     *
     * @param SecurityChecker $securityChecker
     */
    public function __construct(SecurityChecker $securityChecker)
    {
        $this->securityChecker = $securityChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(ConsumerEvent $event, $directory)
    {
        $this->triggerNewStep($event, array('message' => 'Checking vulnerability'));

        try {
            $alerts = $this->securityChecker->check($this->workingTempPath.'/'.$directory.'/composer.lock', 'text');
        } catch (\RuntimeException $e) {
            $this->triggerError($event, array('message' => $e->getMessage()));

            return 1;
        }

        $vulnerabilityCount = $this->securityChecker->getLastVulnerabilityCount();
        if ($vulnerabilityCount > 0) {
            $alerts = str_replace(array("Security Report\n===============\n"), array(''), trim($alerts, "\n"));
            $this->triggerStepError(
                $event,
                array(
                    'message' => 'Vulnerability found : '.$vulnerabilityCount,
                    'alerts' => nl2br($alerts)
                )
            );
        }

        return 0;
    }
}
