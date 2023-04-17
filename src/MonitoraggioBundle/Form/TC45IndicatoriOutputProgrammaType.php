<?php

namespace MonitoraggioBundle\Form;

use MonitoraggioBundle\Form\BaseFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TC45IndicatoriOutputProgrammaType extends BaseFormType {

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('cod_indicatore', self::text, array(
                    'label' => 'Codice indicatore',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'],
                ))
                ->add('cod_indicatore_out', self::text, array(
                    'label' => 'Codice indicatore output',
                    'disabled' => $options['disabled'],
                    'required' => false,
                ))
                ->add('descrizione_indicatore', self::text, array(
                    'label' => 'Descrizione indicatore',
                    'disabled' => $options['disabled'],
                    'required' => false,
                ))
                ->add('unita_misura', self::text, array(
                    'label' => 'Unità di misura',
                    'disabled' => $options['disabled'],
                    'required' => false,
                ))
                ->add('desc_unita_misura', self::text, array(
                    'label' => 'Descrizione unità di misura',
                    'disabled' => $options['disabled'],
                    'required' => false,
                ))
                ->add('programma', self::entity, array(
                    'label' => 'Programma',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    'class' => 'MonitoraggioBundle\Entity\TC4Programma',
                ))
                ->add('fonte_dato', self::text, array(
                    'label' => 'Fonte del dato',
                    'disabled' => $options['disabled'],
                    'required' => false,
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
