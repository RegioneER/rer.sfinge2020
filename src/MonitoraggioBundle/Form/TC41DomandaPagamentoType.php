<?php

namespace MonitoraggioBundle\Form;

use MonitoraggioBundle\Form\BaseFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TC41DomandaPagamentoType extends BaseFormType {

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('id_domanda_pagamento', self::text, array(
                    'label' => 'Domanda pagamento',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'],
                ))
                ->add('programma', self::entity, array(
                    'label' => 'Programma',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    "class" => "MonitoraggioBundle\Entity\TC4Programma",
                ))
                ->add('fondo', self::entity, array(
                    'label' => 'Fondo',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    "class" => "MonitoraggioBundle\Entity\TC33FonteFinanziaria",
                ))
                ->add('submit', self::salva_indietro, array(
                    "url" => $options["url_indietro"],
                    'disabled' => false,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
    }

}
