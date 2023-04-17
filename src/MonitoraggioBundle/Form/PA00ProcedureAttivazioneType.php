<?php

namespace MonitoraggioBundle\Form;

use MonitoraggioBundle\Form\BaseFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PA00ProcedureAttivazioneType extends BaseFormType {

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('cod_proc_att', self::text, array(
                    'label' => 'Codice procedura attivazione',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'],
                ))
                ->add('cod_proc_att_locale', self::text, array(
                    'label' => 'Codice procedura attivazione locale',
                    'disabled' => $options['disabled'],
                    'required' => false,
                ))
                ->add('cod_aiuto_rna', self::text, array(
                    'label' => 'Codice aiuto RNA',
                    'disabled' => $options['disabled'],
                    'required' => false,
                ))
                ->add('tc2_tipo_procedura_attivazione', self::entity, array(
                    'label' => 'Tipo operazione',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    "class" => "MonitoraggioBundle\Entity\TC2TipoProceduraAttivazione",
                ))
                ->add('flag_aiuti', self::choice, array(
                    'label' => 'Flag aiuti',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    'choices_as_values' => true,
                    'choices' => array('Sì' => 'S', 'No' => 'N',),
                ))
                ->add('descr_procedura_att', self::textarea, array(
                    'label' => 'Descrizione procedura attivazione',
                    'disabled' => $options['disabled'],
                    'required' => false,
                ))
                ->add('tc3_responsabile_procedura', self::entity, array(
                    'label' => 'Responsabile procedura',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    'class' => 'MonitoraggioBundle\Entity\TC3ResponsabileProcedura',
                ))
                ->add('denom_resp_proc', self::text, array(
                    'label' => 'denom_resp_proc',
                    'disabled' => $options['disabled'],
                    'required' => false,
                ))
                ->add('data_avvio_procedura', self::birthday, array(
                    'label' => 'Data avvio procedura',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    "widget" => "single_text",
                    "input" => "datetime",
                    "format" => "dd/MM/yyyy",
                ))
                ->add('data_fine_procedura', self::birthday, array(
                    'label' => 'Data fine procedura',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    "widget" => "single_text",
                    "input" => "datetime",
                    "format" => "dd/MM/yyyy",
                ))
                ->add('flg_cancellazione', self::choice, array(
                    'label' => 'Flag cancellazione',
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
