<?php

/*
 * This file is part of `Composer as a service`.
 *
 * (c) Pascal Borreli <pascal@borreli.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ayaline\Bundle\ComposerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Ayaline\Bundle\ComposerBundle\Validator\Constraints\ComposerJson;

class ComposerType extends AbstractType
{
    /**
     * @var string
     */
    private $defaultComposerBody = <<<DCB
{
    "require": {
        "monolog/monolog": "1.2.*"
    }
}
DCB;

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'body',
                'textarea',
                array(
                    'attr' => array(
                        'class' => 'form-control',
                        'rows' => 15,
                        'spellcheck'=> false
                    ),
                    'data' => $this->defaultComposerBody,
                    'constraints' => array(
                        new ComposerJson()
                    ),
                )
            )
            ->add('hasDevDependencies', 'checkbox', array('required' => true))
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'composer';
    }
}
