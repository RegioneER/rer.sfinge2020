<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Form;

use MonitoraggioBundle\Form\BaseType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Description of TC4Programma
 *
 * @author lfontana
 */
class TC16LocalizzazioneGeograficaType extends BaseFormType{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        
        $builder->add('codice_regione', self::text, array(
            'label' => 'Codice regione',
            'disabled' => $options['disabled'],
            'required' => !$options['disabled'],
        ));
        
        $builder->add('descrizione_regione', self::text, array(
            'label' => 'Regione',
            'disabled' => $options['disabled'],
            'required' => false,
        ));
        
        $builder->add('codice_provincia', self::text, array(
            'label' => 'Codice provincia',
            'disabled' => $options['disabled'],
            'required' => !$options['disabled'],
        ));
        
        $builder->add('descrizione_provincia', self::text, array(
            'label' => 'Provincia',
            'disabled' => $options['disabled'],
            'required' => false,
        ));
                
        $builder->add('codice_comune', self::text, array(
            'label' => 'Codice comune',
            'disabled' => $options['disabled'],
            'required' => !$options['disabled'],
        ));
        
        
        $builder->add('descrizione_comune', self::text, array(
            'label' => 'Comune',
            'disabled' => $options['disabled'],
            'required' => false,
        ));
        
        $builder->add('nuts_1', self::text, array(
            'label' => 'NUTS I livello',
            'disabled' => $options['disabled'],
            'required' => false,
        ));
       
        $builder->add('nuts_2', self::text, array(
            'label' => 'NUTS II livello',
            'disabled' => $options['disabled'],
            'required' => false,
        ));
           
        $builder->add('nuts_3', self::text, array(
            'label' => 'NUTS III livello',
            'disabled' => $options['disabled'],
            'required' => false,
        ));
        
        $builder->add('submit',self::salva_indietro, array(
            "url" => $options["url_indietro"], 
            'disabled' => false,
        ));
    }
}
