<?php

namespace MonitoraggioBundle\Form;

use MonitoraggioBundle\Form\BaseFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FN07PagamentiAmmessiType extends BaseFormType {

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
                    'required' => false,
                ))
                ->add('tipologia_pag', self::choice, array(
                    'label' => 'Tipologia pagamento',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    'placeholder' => '-',
                    'choices_as_values' => true,
                    "choices" => array(
                        "Pagamento" => "P",
                        "Rettifica" => "R",
                        "Pagamento per trasferimento" => "P-TR",
                        "Rettifica per trasferimento" => "R-TR",),
                ))
                ->add('data_pagamento', self::birthday, array(
                    'label' => 'Data pagamento',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    "widget" => "single_text",
                    "input" => "datetime",
                    "format" => "dd/MM/yyyy",
                ))
                ->add('data_pag_amm', self::birthday, array(
                    'label' => 'data_pag_amm',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    "widget" => "single_text",
                    "input" => "datetime",
                    "format" => "dd/MM/yyyy",
                ))
                ->add('tipologia_pag_amm', self::choice, array(
                    'label' => 'Tipologia pagamento ammissibile',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    'placeholder' => '-',
                    'choices_as_values' => true,
                    "choices" => array(
                        "Pagamento" => "P",
                        "Rettifica" => "R",
                        "Pagamento per trasferimento" => "P-TR",
                        "Rettifica per trasferimento" => "R-TR",),
                ))
                ->add('importo_pag_amm', self::moneta, array(
                    'label' => 'importo_pag_amm',
                    'disabled' => $options['disabled'],
                    'required' => false,
                ))
                ->add('tc4_programma', self::entity, array(
                    'label' => 'Programma',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    'class' => 'MonitoraggioBundle:TC4Programma',
                ))
                ->add('tc39_causale_pagamento', self::entity, array(
                    'label' => 'Causale pagamento',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    'class' => 'MonitoraggioBundle:TC39CausalePagamento'
                ))
                ->add('tc36_livello_gerarchico', 'MonitoraggioBundle\Form\Type\LivelloGerarchicoType', array(
                    'label' => 'Livello gerarchico',
                    'disabled' => $options['disabled'],
                    'required' => false,
                ))
                ->add('note_pag', self::textarea, array(
                    'label' => 'Note pagamento',
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
