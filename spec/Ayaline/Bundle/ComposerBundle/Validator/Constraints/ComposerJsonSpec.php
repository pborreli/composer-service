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
