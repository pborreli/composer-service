<?php

/*
 * This file is part of `Composer as a service`.
 *
 * (c) Pascal Borreli <pascal@borreli.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Ayaline\Bundle\ComposerBundle\Form\Type;

use Ayaline\Bundle\ComposerBundle\Validator\Constraints\ComposerJson;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Form\FormBuilder;

class ComposerTypeSpec extends ObjectBehavior
{
    public function it_is_form_type()
    {
        $this->shouldHaveType('Symfony\Component\Form\AbstractType');
    }

    public function it_have_name()
    {
        $this->getBlockPrefix()->shouldReturn('composer');
    }

    public function it_add_fields_during_build_form(FormBuilder $builder)
    {
        $builder->add(
            'body',
            \Symfony\Component\Form\Extension\Core\Type\TextareaType::class,
            array(
                'attr' => array(
                    'class' => 'form-control',
                    'rows' => 15,
                    'spellcheck' => false,
                ),
                'data' => $this->getDefaultComposerBody(),
                'constraints' => array(
                    new ComposerJson(),
                ),
            )
        )->shouldBeCalled()->willReturn($builder);

        $builder->add('hasDevDependencies', \Symfony\Component\Form\Extension\Core\Type\CheckboxType::class, array('required' => true))
            ->shouldBeCalled()
            ->willReturn($builder);

        $this->buildForm($builder, array());
    }

    private function getDefaultComposerBody()
    {
        return <<<'DCB'
{
    "require": {
        "monolog/monolog": "1.2.*"
    }
}
DCB;
    }
}
