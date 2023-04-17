<?php

namespace MonitoraggioBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use MonitoraggioBundle\Entity\TC11TipoClassificazione;
use MonitoraggioBundle\Entity\TC4Programma;

class TC12ClassificazioneType extends BaseFormType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);

        $builder->add('tipo_classificazione', self::entity, [
            'disabled' => $options['disabled'],
            'label' => 'Tipo classificazione',
            "class" => TC11TipoClassificazione::class,
        ]);

        $builder->add('programma', self::entity, [
            'label' => 'Programma',
            'disabled' => $options['disabled'],
            'required' => false,
            "class" => TC4Programma::class,
        ]);

        $builder->add('origine_dato', self::text, [
            'required' => false,
            'disabled' => $options['disabled'],
            'label' => 'Origine dato',
        ]);

        $builder->add('codice', self::text, [
            'label' => 'Codice',
            'disabled' => $options['disabled'],
            'required' => !$options['disabled'],
        ]);

        $builder->add('descrizione', self::textarea, [
            'label' => 'Descrizione',
            'disabled' => $options['disabled'],
            'required' => false,
        ]);

        $builder->add('submit', self::salva_indietro, [
            "url" => $options["url_indietro"],
            'disabled' => false,
        ]);
    }
}
