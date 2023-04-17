<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Form;

use MonitoraggioBundle\Form\BaseFormType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Description of TC4Programma
 *
 * @author lfontana
 */
class TC26AtecoType extends BaseFormType{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        
        $builder->add('cod_ateco_anno', self::text, array(
            'label' => 'Codice ATECO',
            'disabled' => $options['disabled'],
            'required' => !$options['disabled'],
        ));
        
        $builder->add('descrizione_codice_ateco', self::textarea, array(
            'label' => 'Descrizione',
            'disabled' => $options['disabled'],
            'required' => false,
        ));
        
        
        $builder->add('submit',self::salva_indietro, array(
            "url" => $options["url_indietro"], 
            'disabled' => false,
        ));
    }
}
