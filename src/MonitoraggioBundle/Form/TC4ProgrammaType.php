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
class TC4ProgrammaType extends BaseFormType{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        
        $builder->add('cod_programma', self::text, array(
            'label' => 'Codice programma',
            'disabled' => $options['disabled'],
            'required' => !$options['disabled'],
        ));
        
        $builder->add('descrizione_programma', self::text, array(
            'label' => 'Descrizione programma',
            'disabled' => $options['disabled'],
            'required' => false,
        ));
        
        $builder->add('fondo', self::text, array(
            'label' => 'Fondo di riferimento',
            'disabled' => $options['disabled'],
            'required' => false,
        ));
        
      
        $builder->add('codice_tipologia_programma', self::text, array(
            'label' => 'Codice tipologia programma',
            'disabled' => $options['disabled'],
            'required' => false,
        ));
        
        
        $builder->add('submit',self::salva_indietro, array(
            "url" => $options["url_indietro"], 
            'disabled' => false,
        ));
    }
}
