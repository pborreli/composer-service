<?php

namespace spec\Ayaline\Bundle\ComposerBundle;

use PhpSpec\ObjectBehavior;

class AyalineComposerBundleSpec extends ObjectBehavior
{
    function it_is_bundle()
    {
        $this->shouldHaveType('Symfony\Component\HttpKernel\Bundle\Bundle');
    }
}
