<?php

/*
 * This file is part of `Composer as a service`.
 *
 * (c) Pascal Borreli <pascal@borreli.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Ayaline\Bundle\ComposerBundle\Consumer;

use Ayaline\Bundle\ComposerBundle\Consumer\Step\StepInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sonata\NotificationBundle\Consumer\ConsumerEvent;

class UploadComposerConsumerSpec extends ObjectBehavior
{
    public function it_is_consumer()
    {
        $this->beConstructedWith(array());
        $this->shouldBeAnInstanceOf('Sonata\NotificationBundle\Consumer\ConsumerInterface');
    }

    public function it_execute_all_steps_and_return_0_status_code(
        ConsumerEvent $event,
        StepInterface $step1,
        StepInterface $step2
    ) {
        $this->beConstructedWith(array(
            $step1, $step2,
        ));

        $step1->execute($event, Argument::type('string'))->shouldBeCalled()->willReturn(0);
        $step2->execute($event, Argument::type('string'))->shouldBeCalled()->willReturn(0);

        $this->process($event)->shouldReturn(0);
    }

    public function it_interrupt_the_execution_of_steps_when_any_of_them_return_non_zero_status(
        ConsumerEvent $event,
        StepInterface $step1,
        StepInterface $step2
    ) {
        $this->beConstructedWith(array(
            $step1, $step2,
        ));

        $step1->execute($event, Argument::type('string'))->shouldBeCalled()->willReturn(5);
        $step2->execute(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->process($event)->shouldReturn(5);
    }
}
