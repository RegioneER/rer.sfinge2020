<?php

namespace CertificazioniBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistroDebitoriType extends CommonType {
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('sospetta_frode', self::choice, [
            'choices_as_values' => true,
            'choices' => [
                'SI' => true,
                'NO' => false,
            ],
            "required" => false,
        ]);

        $builder->add('olaf', self::choice, [
            'choices_as_values' => true,
            'choices' => [
                'SI' => true,
                'NO' => false,
            ],
            "required" => false,
        ]);

        $builder->add('spesa_irregolare', self::importo, [
            "label" => "Spesa irregolare",
            'disabled' => false,
            "currency" => "EUR",
            "grouping" => true,
            'required' => true,
        ]);

        $builder->add('tipo_iter_recupero', self::entity, [
            'class' => 'CertificazioniBundle\Entity\TipoIterRecupero',
            'placeholder' => '-',
            'required' => false,
            'label' => 'Procedura attivata',
        ]);

        $builder->add('nota_iter', self::AdvancedTextType, [
            'required' => false,
            'disabled' => false,
            'label' => 'Note',
        ]);

        $builder->add('restituzione_rateizzata', self::choice, [
            'choices_as_values' => true,
            'choices' => [
                'SI' => true,
                'NO' => false,
            ],
            "required" => false,
        ]);

        $builder->add("pulsanti", self::salva_indietro, ["url" => $options["url_indietro"]]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => 'CertificazioniBundle\Entity\RegistroDebitori',
        ]);

        $resolver->setRequired("url_indietro");
    }
}
