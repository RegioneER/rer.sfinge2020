<?php

namespace MonitoraggioBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IN00IndicatoriRisultatoType extends BaseFormType {
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('cod_locale_progetto', self::text, [
            'label' => 'Codice locale progetto',
            'disabled' => $options['disabled'],
            'required' => !$options['disabled'],
        ])
                ->add('tipo_indicatore_di_risultato', self::choice, [
                    'label' => 'Tipo indicatore risultato',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'],
                    'choices_as_values' => true,
                    'choices' => [
                        "Comune nazionale/comunitario" => "COM",
                        "Definito dal programma" => "DPR",
                    ],
                ])
                ->add('indicatore_id', self::entity, [
                    'class' => 'MonitoraggioBundle\Entity\TC42_43IndicatoriRisultato',
                    'label' => 'Indicatore',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'],
                ])
                ->add('flg_cancellazione', self::choice, [
                    'label' => 'Cancellato',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    'choices_as_values' => true,
                    'choices' => ['Sì' => 'S'],
                    'placeholder' => 'No',
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
