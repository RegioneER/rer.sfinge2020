<?php

namespace MonitoraggioBundle\Form;

use MonitoraggioBundle\Form\BaseFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AP03ClassificazioniType extends BaseFormType {

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('cod_locale_progetto', self::text, array(
                    'label' => 'Codice locale progetto',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'],
                ))
                ->add('classificazione', self::entity, array(
                    'class' => 'MonitoraggioBundle\Entity\TC12Classificazione',
                    'label' => 'Codice classificazione',
                    'disabled' => $options['disabled'],
                    'required' => false,
                ))
                ->add('tc4_programma', self::entity, array(
                    'label' => 'Programma',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    "class" => "MonitoraggioBundle\Entity\TC4Programma"
                ))
                ->add('tc11_tipo_classificazione', self::entity, array(
                    'label' => 'Tipo progetto complesso',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    "class" => "MonitoraggioBundle\Entity\TC11TipoClassificazione"
                ))
                ->add('flg_cancellazione', self::choice, array(
                    'label' => 'Falg cancellazione',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    'placeholder' => 'No',
                    'choices_as_values' => true,
                    'choices' => array('Sì' => 'S'),
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
