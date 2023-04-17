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
 * Description of TC2TipoProceduraAttivazioneType
 *
 * @author lfontana
 */
class TC2TipoProceduraAttivazioneType extends BaseFormType{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        
        $builder->add('tip_procedura_att', self::text, array(
            'label' => 'Tipo procedura attivazione',
            'required' => true,
            'disabled' => $options['disabled'],
        ));
        $builder->add('cod_proc_att_locale', self::textarea, array(
            'label' => 'Descrizione procedura attivazione',
            'required' => false,
            'disabled' => $options['disabled'],
        ));
        
        $builder->add('submit',self::salva_indietro, array(
            "url" => $options["url_indietro"], 
            'disabled' => false,
        ));
        
    }

    
}