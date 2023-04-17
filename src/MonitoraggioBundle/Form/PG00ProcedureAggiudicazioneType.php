<?php

namespace MonitoraggioBundle\Form;

use MonitoraggioBundle\Form\BaseFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PG00ProcedureAggiudicazioneType extends BaseFormType {

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('cod_locale_progetto', self::text, array(
                    'label' => 'Codice locale progetto',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'],
                ))
                ->add('cod_proc_agg', self::text, array(
                    'label' => 'Codice procedura aggiudicazione',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'],
                ))
                ->add('cig', self::text, array(
                    'label' => 'Codice identificativo gara',
                    'disabled' => $options['disabled'],
                    'required' => false,
                ))
                ->add('descr_procedura_agg', self::text, array(
                    'label' => 'Descrizione procedura aggiudicazione',
                    'disabled' => $options['disabled'],
                    'required' => false,
                ))
                ->add('importo_procedura_agg', self::text, array(
                    'label' => 'Importo base della procedura',
                    'disabled' => $options['disabled'],
                    'required' => false,
                ))
                ->add('data_pubblicazione', self::text, array(
                    'label' => 'Data pubblicazione',
                    'disabled' => $options['disabled'],
                    'required' => false,
                ))
                ->add('importo_aggiudicato', self::text, array(
                    'label' => 'Importo aggiudicato',
                    'disabled' => $options['disabled'],
                    'required' => false,
                ))
                ->add('data_aggiudicazione', self::text, array(
                    'label' => 'Data aggiudicazione',
                    'disabled' => $options['disabled'],
                    'required' => false,
                ))
                ->add('tc22_motivo_assenza_cig', self::entity, array(
                    'label' => 'Motivo assenza CIG',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    'class' => 'MonitoraggioBundle\Entity\TC22MotivoAssenzaCIG',
                ))
                ->add('tc23_tipo_procedura_aggiudicazione', self::entity, array(
                    'label' => 'Tipologia procedura aggiudicazione',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    'class' => 'MonitoraggioBundle\Entity\TC23TipoProceduraAggiudicazione',
                ))
                ->add('flg_cancellazione', self::choice, array(
                    'label' => 'Flag cancellato',
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
