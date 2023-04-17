<?php

namespace MonitoraggioBundle\Form;

use MonitoraggioBundle\Form\BaseFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FN09SpeseCertificateType extends BaseFormType {

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('cod_locale_progetto', self::text, array(
                    'label' => 'Codice locale progetto',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'],
                ))
                ->add('data_domanda', self::birthday, array(
                    'label' => 'Data domanda',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'],
                    "widget" => "single_text",
                    "input" => "datetime",
                    "format" => "dd/MM/yyyy",
                ))
                ->add('tipologia_importo', self::choice, array(
                    'label' => 'Tipologia pagamento',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'],
                    'choices_as_values' => true,
                    'choices' => array(
                        "Certificato" => 'C', 
                        "Decertificato" => 'D',
                    ),
                ))
                ->add('importo_spesa_tot', self::moneta, array(
                    'label' => 'Importo spesa totale',
                    'disabled' => $options['disabled'],
                    'required' => false,
                ))
                ->add('importo_spesa_pub', self::moneta, array(
                    'label' => 'Importo spesa ammmissibile',
                    'disabled' => $options['disabled'],
                    'required' => false,
                ))
                ->add('tc41_domande_pagamento', self::entity, array(
                    'label' => 'Domanda di pagamento',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'],
                    'class' => 'MonitoraggioBundle\Entity\TC41DomandaPagamento',
                ))
                ->add('tc36_livello_gerarchico', 'MonitoraggioBundle\Form\Type\LivelloGerarchicoType', array(
                    'label' => 'Livello gerarchico',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'],
                ))
                ->add('flg_cancellazione', self::choice, array(
                    'label' => 'Cancellato',
                    'disabled' => $options['disabled'],
                    'required' => false,
					'choices_as_values' => true,
                    'choices' => array('Sì' => 'S'),
                    'placeholder' => 'No',
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
