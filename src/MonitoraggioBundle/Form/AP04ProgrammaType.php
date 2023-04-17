<?php

namespace MonitoraggioBundle\Form;

use MonitoraggioBundle\Form\BaseFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AP04ProgrammaType extends BaseFormType {

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('cod_locale_progetto', self::text, array(
                    'label' => 'Codice locale progetto',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'],
                ))
                ->add('tc4_programma', self::entity, array(
                    'label' => 'Programma',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    "class" => "MonitoraggioBundle\Entity\TC4Programma",
                ))
                ->add('stato', self::choice, array(
                    'label' => 'Stato',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    'choices_as_values' => true,
                    'choices' => array(
                        'Attivo' => 1,
                        'Non attivo' => 2,
                    ),
                ))
                ->add('tc14_specifica_stato', self::entity, array(
                    'label' => 'Specifica stato',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    "class" => "MonitoraggioBundle\Entity\TC14SpecificaStato",
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
