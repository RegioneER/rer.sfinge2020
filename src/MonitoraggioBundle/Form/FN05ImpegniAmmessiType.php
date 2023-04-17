<?php

namespace MonitoraggioBundle\Form;

use MonitoraggioBundle\Form\BaseFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FN05ImpegniAmmessiType extends BaseFormType {

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
                    'required' => !$options['disabled'],
                ))
                ->add('tipologia_impegno', self::choice, array(
                    'label' => 'Tipologia impegno',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'],
                    "placeholder" => '-',
                    'choices_as_values' => true,
                    "choices" => array(
                        "Impegno" => "I",
                        "Disimpegno" => "DI",
                        "Impegno per trasferimento" => "I-TR",
                        "Diseimpegno per trasferimento" => "D-TR",),
                ))
                ->add('data_impegno', self::birthday, array(
                    'label' => 'Data impegno',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'],
                    "widget" => "single_text",
                    "input" => "datetime",
                    "format" => "dd/MM/yyyy",
                ))
                ->add('tc4_programma', self::entity, array(
                    'label' => 'Programma',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'],
                    'class' => 'MonitoraggioBundle\Entity\TC4Programma',
                ))
                
                 ->add('tc36_livello_gerarchico', 'MonitoraggioBundle\Form\Type\LivelloGerarchicoType', array(
                    'label' => 'Livello gerarchico',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'],
                ))
                
                
                ->add('data_imp_amm', self::birthday, array(
                    'label' => 'Data impegno amesso',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'],
                    "widget" => "single_text",
                    "input" => "datetime",
                    "format" => "dd/MM/yyyy",
                ))
                ->add('tipologia_imp_amm', self::choice, array(
                    'label' => 'Tipologia impegno amesso',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'],
                    "placeholder" => '-',
                    'choices_as_values' => true,
                    "choices" => array(
                        "Impegno" => "I",
                        "Disimpegno" => "DI",
                        "Impegno per trasferimento" => "I-TR",
                        "Diseimpegno per trasferimento" => "D-TR",),
                ))
                
                ->add('tc38_causale_disimpegno_amm', self::entity, array(
                    'label' => 'tc38_causale_disimpegno',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'],
                    'class' => 'MonitoraggioBundle\Entity\TC38CausaleDisimpegno'
                ))
                
                ->add('importo_imp_amm', self::moneta, array(
                    'label' => 'Importo impegno ammesso',
                    'disabled' => $options['disabled'],
                    'required' => false,
                ))
                ->add('note_imp', self::textarea, array(
                    'label' => 'Note impegno ammesso',
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
