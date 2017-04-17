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

use SensioLabs\Security\SecurityChecker;
use Sonata\NotificationBundle\Consumer\ConsumerEvent;

/**
 * @author Hubert Moutot <hubert.moutot@gmail.com>
 */
class CheckVulnerabilitiesStep extends AbstractStep
{
    protected $securityChecker;

    /**
     * Constructor.
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
        $this->triggerNewStep($event, ['message' => 'Checking vulnerability']);

        try {
            $alerts = $this->securityChecker->check($this->workingTempPath.'/'.$directory.'/composer.lock');
        } catch (\RuntimeException $e) {
            $this->triggerError($event, ['message' => $e->getMessage()]);

            return 1;
        }

        $vulnerabilityCount = $this->securityChecker->getLastVulnerabilityCount();
        if ($vulnerabilityCount > 0) {
            $this->triggerStepError($event, ['message' => 'Vulnerability found : '.$vulnerabilityCount]);
            $this->triggerVulnerabilities($event, ['message' => json_encode($alerts)]);
        }

        return 0;
    }
}
