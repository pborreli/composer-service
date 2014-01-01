<?php

namespace Ayaline\Bundle\ComposerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ComposerType extends AbstractType
{
    private $defaultComposerBody = <<<DCB
{
    "require": {
        "monolog/monolog": "1.2.*"
    }
}
DCB;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('body', 'textarea', array(
                    'attr' => array(
                        'class' => 'form-control',
                        'rows' => 15,
                    ),
                    'data' => $this->defaultComposerBody,
                )
            )
            ->add('hasDevDependencies', 'checkbox', array(
                    'required' => true,
                )
            )
        ;
    }

    public function getName()
    {
        return 'composer';
    }
}
