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
interface StepInterface
{
    /**
     * Executes a step logic.
     *
     * @param ConsumerEvent $event
     * @param string        $directory
     *
     * @return int the exit status (0 if successful, non-zero otherwise)
     */
    public function execute(ConsumerEvent $event, $directory);
}
