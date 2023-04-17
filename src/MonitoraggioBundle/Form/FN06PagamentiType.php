<?php

namespace MonitoraggioBundle\Form;

use MonitoraggioBundle\Form\BaseFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FN06PagamentiType extends BaseFormType {

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('cod_locale_progetto', self::text, array(
                    'label' => 'Codice locale progetto',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'],
                ))
                ->add('cod_pagamento', self::text, array(
                    'label' => 'Codice pagamento',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'],
                ))
                ->add('tipologia_pag', self::choice, array(
                    'label' => 'Tipologia pagamento',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'],
                    "placeholder" => '-',
                    'choices_as_values' => true,
                    "choices" => array(
                        "Pagamento" => "P",
                        "Rettifica" => "R",
                        "Pagamento per trasferimento" => "P-TR",
                        "Rettifica per trasferimento" => "R-TR",),
                ))
                ->add('data_pagamento', self::birthday, array(
                    'label' => 'Data impegno',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    "widget" => "single_text",
                    "input" => "datetime",
                    "format" => "dd/MM/yyyy",
                ))
                ->add('importo_pag', self::moneta, array(
                    'label' => 'Importo pagamento',
                    'disabled' => $options['disabled'],
                    'required' => false,
                ))
                ->add('tc39_causale_pagamento', self::entity, array(
                    'label' => 'Causale pagamento',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    'class' => 'MonitoraggioBundle:TC39CausalePagamento',
                ))
                ->add('note_pag', self::textarea, array(
                    'label' => 'Note',
                    'disabled' => $options['disabled'],
                    'required' => false,
                ))
                ->add('flg_cancellazione', self::choice, array(
                    'label' => 'Cancellato',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    'choices_as_values' => true,
                    'choices' => array(
                        'Sì' => 'S'
                    ),
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
