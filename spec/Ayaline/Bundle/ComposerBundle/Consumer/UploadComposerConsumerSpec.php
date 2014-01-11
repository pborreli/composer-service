<?php

namespace spec\Ayaline\Bundle\ComposerBundle\Consumer;

use Ayaline\Bundle\ComposerBundle\Consumer\Step\StepInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sonata\NotificationBundle\Consumer\ConsumerEvent;

class UploadComposerConsumerSpec extends ObjectBehavior
{
    function it_is_consumer()
    {
        $this->beConstructedWith(array());
        $this->shouldBeAnInstanceOf('Sonata\NotificationBundle\Consumer\ConsumerInterface');
    }

    function it_execute_all_steps_and_return_0_status_code(
        ConsumerEvent $event,
        StepInterface $step1,
        StepInterface $step2
    ){
        $this->beConstructedWith(array(
            $step1, $step2
        ));

        $step1->execute($event, Argument::type('string'))->shouldBeCalled()->willReturn(0);
        $step2->execute($event, Argument::type('string'))->shouldBeCalled()->willReturn(0);

        $this->process($event)->shouldReturn(0);
    }

    function it_interrupt_the_execution_of_steps_when_any_of_them_return_non_zero_status(
        ConsumerEvent $event,
        StepInterface $step1,
        StepInterface $step2
    ){
        $this->beConstructedWith(array(
            $step1, $step2
        ));

        $step1->execute($event, Argument::type('string'))->shouldBeCalled()->willReturn(5);
        $step2->execute(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->process($event)->shouldReturn(5);
    }
}
