<?php

namespace spec\Ayaline\Bundle\ComposerBundle\Form;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Form\FormBuilder;

class ComposerTypeSpec extends ObjectBehavior
{
    function it_is_form_type()
    {
        $this->shouldHaveType('Symfony\Component\Form\AbstractType');
    }

    function it_have_name()
    {
        $this->getName()->shouldReturn('composer');
    }

    function it_add_fields_during_build_form(FormBuilder $builder)
    {
        $builder->add(
                'body',
                'textarea',
                array(
                    'attr' => array(
                        'class' => 'form-control',
                        'rows' => 15,
                    ),
                    'data' => $this->getDefaultComposerBody()
                ,
                )
            )->shouldBeCalled()
            ->willReturn($builder);

        $builder->add('hasDevDependencies', 'checkbox', array('required' => true))
            ->shouldBeCalled()
            ->willReturn($builder);

        $this->buildForm($builder, array());
    }

    private function getDefaultComposerBody()
    {
        return <<<DCB
{
    "require": {
        "monolog/monolog": "1.2.*"
    }
}
DCB;
    }
}
