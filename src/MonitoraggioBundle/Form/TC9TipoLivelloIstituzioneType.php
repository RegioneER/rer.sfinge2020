<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Form;

/**
 * Description of TC9TipoLivelloIstituzioneType
 *
 * @author lfontana
 */
class TC9TipoLivelloIstituzioneType extends BaseFormType{
    
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        
        $builder->add('liv_istituzione_str_fin', self::text, array(
            'label' => 'Codice progetto',
            'disabled' => $options['disabled'],
            'required' => !$options['disabled'],
        ));
        
        $builder->add('descrizione_livello_istituzione', self::textarea, array(
            'label' => 'Descrizione progetto',
            'disabled' => $options['disabled'],
            'required' => false,
        ));
        

                
        $builder->add('submit',self::salva_indietro, array(
            "url" => $options["url_indietro"], 
            'disabled' => false,
        ));
    }

}
