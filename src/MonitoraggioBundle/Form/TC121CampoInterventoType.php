<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Form;

/**
 * Description of TC121CampoInterventoType
 *
 * @author lfontana
 */
class TC121CampoInterventoType extends BaseFormType{
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);

        $builder->add('cod_classificazione_ci', self::text, array(
            'disabled' => $options['disabled'],
            'required' => !$options['disabled'],
            'label' => 'Codice campo di intervento',
                ));

        $builder->add('desc_classificazione_ci', self::textarea, array(
            'disabled' => $options['disabled'],
            'required' => false,
            'label' => 'Codice campo di intervento',
                ));

        $builder->add('spec_macroaggr_ci', self::text, array(
            'disabled' => $options['disabled'],
            'required' => false,
            'label' => 'Descrizione campo di intervento',
        ));
        $builder->add('cod_macroaggr_ci', self::text, array(
            'disabled' => $options['disabled'],
            'required' => false,
            'label' => 'Codice della specifica del macroaggregato',
        ));
        $builder->add('desc_macroaggr_ci', self::text, array(
            'disabled' => $options['disabled'],
            'required' => false,
            'label' => 'Descrizione del codice del macroaggregato',
        ));
        $builder->add('origine_dato', self::text, array(
            'disabled' => $options['disabled'],
            'required' => false,
            'label' => 'Origine dato',
        ));
        $builder->add('submit',self::salva_indietro, array(
            "url" => $options["url_indietro"], 
            'disabled' => false,
        ));

    }
}
