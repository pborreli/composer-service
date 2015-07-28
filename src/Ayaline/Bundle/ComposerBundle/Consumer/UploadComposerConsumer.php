<?php

/*
 * This file is part of `Composer as a service`.
 *
 * (c) Pascal Borreli <pascal@borreli.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ayaline\Bundle\ComposerBundle\Consumer;

use Sonata\NotificationBundle\Consumer\ConsumerEvent;
use Sonata\NotificationBundle\Consumer\ConsumerInterface;

class UploadComposerConsumer implements ConsumerInterface
{
    protected $steps;

    /**
     * Constructor.
     *
     * @param array $steps
     */
    public function __construct(array $steps)
    {
        $this->steps = $steps;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ConsumerEvent $event)
    {
        $directory = uniqid('composer', true);

        foreach ($this->steps as $step) {
            if (0 !== $return = $step->execute($event, $directory)) {
                return $return;
            }
        }

        return 0;
    }
}
