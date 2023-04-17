<?php

namespace MonitoraggioBundle\Form;

use MonitoraggioBundle\Form\BaseFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AP06LocalizzazioneGeograficaType extends BaseFormType {

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('cod_locale_progetto', self::text, array(
                    'label' => 'Codice locale progetto',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'],
                ))
                ->add('localizzazioneGeografica', 'MonitoraggioBundle\Form\Type\LocalizzazioneGeograficaType', array(
                    'label' => false,
                    'disabled' => $options['disabled'],
                    'required' => false,
                ))
                ->add('indirizzo', self::text, array(
                    'label' => 'Indirizzo',
                    'disabled' => $options['disabled'],
                    'required' => false,
                ))
                ->add('cod_cap', self::text, array(
                    'label' => 'Codice CAP',
                    'disabled' => $options['disabled'],
                    'required' => false,
                ))
                ->add('flg_cancellazione', self::choice, array(
                    'label' => 'Flag cancellazione',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    'choices_as_values' => true,
                    'choices' => array(
                        'Cancellato' => 'S'
                    ),
                    'placeholder' => 'Non cancellato',
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
