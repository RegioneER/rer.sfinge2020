<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Form;

/**
 * Description of TC11TipoClassificazioneType
 *
 * @author lfontana
 */
class TC11TipoClassificazioneType extends BaseFormType{
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        
        $builder->add('tipo_class', self::text, array(
            'disabled' => $options['disabled'],
            'required' => !$options['disabled'],
            'label' => 'Tipo classificazione',
                ));

        $builder->add('descrizione_tipo_classificazione', self::textarea, array(
            'disabled' => $options['disabled'],
            'required' => false,
            'label' => 'Descrizione',
                ));

        $builder->add('origine_classificazione', self::text, array(
            'disabled' => $options['disabled'],
            'required' => false,
            'label' => 'Origine classificazione',
        ));
        $builder->add('submit',self::salva_indietro, array(
            "url" => $options["url_indietro"], 
            'disabled' => false,
        ));
        
    }
}
