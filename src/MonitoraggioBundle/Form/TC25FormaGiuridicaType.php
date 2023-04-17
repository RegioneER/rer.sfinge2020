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
class TC25FormaGiuridicaType extends BaseFormType{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        
        $builder->add('forma_giuridica', self::text, array(
            'label' => 'Codice ISTAT forma giuridica',
            'disabled' => $options['disabled'],
            'required' => !$options['disabled'],
        ));
        
        $builder->add('descrizione_forma_giuridica', self::textarea, array(
            'label' => 'Descrizione ISTAT forma giuridica',
            'disabled' => $options['disabled'],
            'required' => false,
        ));
        
        $builder->add('divisione', self::text, array(
            'label' => 'Divisione',
            'disabled' => $options['disabled'],
            'required' => false,
        ));
        
        $builder->add('sezione', self::text, array(
            'label' => 'Sezione',
            'disabled' => $options['disabled'],
            'required' => false,
        ));
        
        
        $builder->add('submit',self::salva_indietro, array(
            "url" => $options["url_indietro"], 
            'disabled' => false,
        ));
    }
}
