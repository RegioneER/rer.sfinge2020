<?php

namespace MonitoraggioBundle\Form;

use MonitoraggioBundle\Form\BaseFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AP01AssociazioneProgettiProceduraType extends BaseFormType {

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('cod_locale_progetto', self::text, array(
                    'label' => 'Codice locale progetto',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'],
                ))
                ->add('tc1_procedura_attivazione', self::entity, array(
                    'label' => 'Procedura attivazione',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    'class' => 'MonitoraggioBundle:TC1ProceduraAttivazione',
                ))
                ->add('flg_cancellazione', self::choice, array(
                    'label' => 'Flag cancellazione',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    'choices' => array('Sì' => 'S'),
                    'choices_as_values' => true,
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
