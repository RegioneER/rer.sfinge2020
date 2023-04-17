<?php

namespace MonitoraggioBundle\Form;

use MonitoraggioBundle\Form\BaseFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FN04ImpegniType extends BaseFormType {

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('cod_locale_progetto', self::text, array(
                    'label' => 'Codice locale progetto',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'],
                ))
                ->add('cod_impegno', self::text, array(
                    'label' => 'Codice impegno',
                    'disabled' => $options['disabled'],
                    'required' => false,
                ))
                ->add('tipologia_impegno', self::choice, array(
                    'label' => 'Tipologia impegno',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    "placeholder" => '-',
                    'choices_as_values' => true,
                    "choices" => array(
                        "Impegno" => "I",
                        "Disimpegno" => "DI",
                        "Impegno per trasferimento" => "I-TR",
                        "Diseimpegno per trasferimento" => "D-TR",),
                ))
                ->add('tc38_causale_disimpegno', self::entity, array(
                    'label' => 'Causale disimpegno',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    'class' => 'MonitoraggioBundle\Entity\TC38CausaleDisimpegno',
                ))
                ->add('data_impegno', self::birthday, array(
                    'label' => 'Data impegno',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    "widget" => "single_text",
                    "input" => "datetime",
                    "format" => "dd/MM/yyyy",
                ))
                ->add('importo_impegno', self::moneta, array(
                    'label' => 'Importo impegno',
                    'disabled' => $options['disabled'],
                    'required' => false,
                ))
                ->add('note_impegno', self::textarea, array(
                    'label' => 'Note impegno',
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
