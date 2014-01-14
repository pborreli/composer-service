<?php

/*
 * This file is part of `Composer as a service`.
 *
 * (c) Pascal Borreli <pascal@borreli.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Ayaline\Bundle\ComposerBundle\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

use Symfony\Component\Validator\ExecutionContext;
use Ayaline\Bundle\ComposerBundle\Validator\Constraints\ComposerJson;

class ComposerJsonValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContext $context)
    {
        $this->initialize($context);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Ayaline\Bundle\ComposerBundle\Validator\Constraints\ComposerJsonValidator');
    }

    function it_is_a_constraint_validator()
    {
        $this->shouldImplement('Symfony\Component\Validator\ConstraintValidator');
    }

    function it_adds_violation_if_the_given_value_is_not_valid_json($context, ComposerJson $constraint)
    {
        $context->addViolation(Argument::any())->shouldBeCalled();

        $this->validate('not valid json', $constraint);
    }

    function it_does_not_add_violation_if_the_given_value_is_valid_json($context, ComposerJson $constraint)
    {
        $composerJsonContent = <<<EOT
{
    "require": {
        "monolog/monolog": "1.2.*"
    }
}
EOT;
        
        $context->addViolation(Argument::any())->shouldNotBeCalled();

        $this->validate($composerJsonContent, $constraint);
    }
}
