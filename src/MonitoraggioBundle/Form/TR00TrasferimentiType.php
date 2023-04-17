<?php

namespace MonitoraggioBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TR00TrasferimentiType extends BaseFormType {
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('cod_trasferimento', self::text, [
            'label' => 'Codice trasferimento',
            'disabled' => $options['disabled'],
            'required' => !$options['disabled'],
        ])
                ->add('data_trasferimento', self::birthday, [
                    'label' => 'Data trasferimento',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'],
                    "widget" => "single_text",
                    "input" => "datetime",
                    "format" => "dd/MM/yyyy",
                ])
                ->add('importo_trasferimento', self::moneta, [
                    'label' => 'Importo trasferimento',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'],
                ])
                ->add('cf_sog_ricevente', self::text, [
                    'label' => 'Codice fiscale ricevente',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'],
                ])
                ->add('flag_soggetto_pubblico', self::choice, [
                    'label' => 'Soggetto pubblico',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'],
                    'choices_as_values' => true,
                    'choices' => [
                        'Sì' => 'S',
                        'No' => 'N',
                    ],
                ])
                ->add('tc4_programma', self::entity, [
                    'label' => 'Programma',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'],
                    'class' => 'MonitoraggioBundle\Entity\TC4Programma',
                ])
                ->add('tc49_causale_trasferimento', self::entity, [
                    'label' => 'Causale trasferimento',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'],
                    'class' => 'MonitoraggioBundle\Entity\TC49CausaleTrasferimento',
                ])
                ->add('flg_cancellazione', self::choice, [
                    'label' => 'Flag cancellazione',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    'placeholder' => 'No',
                    'choices_as_values' => true,
                    'choices' => ['Sì' => 'S', ],
                ])
                ->add('submit', self::salva_indietro, [
                    "url" => $options["url_indietro"],
                    'disabled' => false,
                ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
    }
}
