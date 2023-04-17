<?php

namespace RichiesteBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use RichiesteBundle\Entity\IndicatoreOutput;


class IndicatoreOutputBeneficiarioType extends CommonType
{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('val_programmato', self::numero,[
            'label' => 'Valore programmato',
            'required' => true,
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver
        ->setDefaults([
            'data_class' => IndicatoreOutput::class,
            'validation_groups' => [
                'Default',
                'presentazione_beneficiario',
            ]
        ]);
    }
}