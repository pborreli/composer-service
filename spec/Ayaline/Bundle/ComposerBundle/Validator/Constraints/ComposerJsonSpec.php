<?php

namespace spec\Ayaline\Bundle\ComposerBundle\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ComposerJsonSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Ayaline\Bundle\ComposerBundle\Validator\Constraints\ComposerJson');
    }

    function it_is_a_validation_constraint()
    {
        $this->shouldHaveType('Symfony\Component\Validator\Constraint');
    }
}
