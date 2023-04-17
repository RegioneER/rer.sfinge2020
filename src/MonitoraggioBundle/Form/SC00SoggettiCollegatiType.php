<?php

namespace MonitoraggioBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SC00SoggettiCollegatiType extends BaseFormType {
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('cod_locale_progetto', self::text, [
            'label' => 'Codice progetto locale"',
            'disabled' => $options['disabled'],
            'required' => !$options['disabled'],
        ])
                ->add('codice_fiscale', self::text, [
                    'label' => 'Codice fiscale',
                    'disabled' => $options['disabled'],
                    'required' => false,
                ])
                ->add('flag_soggetto_pubblico', self::choice, [
                    'label' => 'Soggetto pubblico',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    'choices_as_values' => true,
                    'choices' => [
                        'Sì' => 'S',
                        'No' => 'N', ],
                ])
                ->add('cod_uni_ipa', self::text, [
                    'label' => 'Codice indice pubblica amministrazione',
                    'disabled' => $options['disabled'],
                    'required' => false,
                ])
                ->add('denominazione_sog', self::text, [
                    'label' => 'Denominazione soggetto',
                    'disabled' => $options['disabled'],
                    'required' => false,
                ])
                ->add('tc24_ruolo_soggetto', self::entity, [
                    'label' => 'Ruolo soggetto',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    'class' => 'MonitoraggioBundle\Entity\TC24RuoloSoggetto',
                ])
                ->add('tc25_forma_giuridica', self::entity, [
                    'label' => 'Forma giuridica',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    'class' => 'MonitoraggioBundle\Entity\TC25FormaGiuridica',
                ])
                ->add('tc26_ateco', self::entity, [
                    'label' => 'Classificazione ATECO',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    'class' => 'MonitoraggioBundle\Entity\TC26Ateco',
                ])
                ->add('note', self::textarea, [
                    'label' => 'Note',
                    'disabled' => $options['disabled'],
                    'required' => false,
                ])
                ->add('flg_cancellazione', self::choice, [
                    'label' => 'Falg cancellato',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    'placeholder' => 'No',
                    'choices_as_values' => true,
                    'choices' => ['Sì' => 'S', ],
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
