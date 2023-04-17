<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Form;

/**
 * Description of TC7ProgettoComplessoType
 *
 * @author lfontana
 */
class TC7ProgettoComplessoType extends BaseFormType{
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        
        $builder->add('cod_prg_complesso', self::text, array(
            'label' => 'Codice progetto',
            'disabled' => $options['disabled'],
            'required' => !$options['disabled'],
        ));
        
        $builder->add('descrizione_progetto_complesso', self::text, array(
            'label' => 'Descrizione progetto',
            'disabled' => $options['disabled'],
            'required' => false,
        ));
        
        $builder->add('cod_programma', self::text, array(
            'label' => 'Codice programma',
            'disabled' => $options['disabled'],
            'required' => false,
        ));
        
        $builder->add('codice_tipo_complessita', self::text, array(
            'label' => 'Codice tipo complessità',
            'disabled' => $options['disabled'],
            'required' => false,
        ));
        
        $builder->add('descrizione_tipo_complessita', self::text, array(
            'label' => 'Descrizione tipo complessità',
            'disabled' => $options['disabled'],
            'required' => false,
        ));
                
        $builder->add('submit',self::salva_indietro, array(
            "url" => $options["url_indietro"], 
            'disabled' => false,
        ));
    }

}
