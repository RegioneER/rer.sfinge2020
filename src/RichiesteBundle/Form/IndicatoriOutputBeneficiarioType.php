<?php

namespace RichiesteBundle\Form;

use BaseBundle\Form\CommonType;
use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;


class IndicatoriOutputBeneficiarioType extends CommonType
{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('mon_indicatore_output', self::collection,[
            'entry_type' => IndicatoreOutputBeneficiarioType::class,
            'label' => false,
            'required' => true,
            'constraints' => [new Valid()]
        ])
        ->add('submit', self::salva_indietro, [
            'url' => $options['url_indietro']
        ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setRequired('url_indietro')
        ->setDefaults([
            'data_class' => Richiesta::class,
            'validation_groups' => [
                'Default',
                'presentazione_beneficiario',
            ]
        ]);
    }
}