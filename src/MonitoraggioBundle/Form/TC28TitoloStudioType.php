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
class TC28TitoloStudioType extends BaseFormType{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        
        $builder->add('titolo_studio', self::text, array(
            'label' => 'Codice titolo di studio',
            'disabled' => $options['disabled'],
            'required' => !$options['disabled'],
        ));
        
        $builder->add('descrizione_titolo_studio', self::textarea, array(
            'label' => 'Descrizione',
            'disabled' => $options['disabled'],
            'required' => false,
        ));
        
        $builder->add('isced', self::text, array(
            'label' => 'Livello ISCED',
            'disabled' => $options['disabled'],
            'required' => false,
        ));
         
        $builder->add('submit',self::salva_indietro, array(
            "url" => $options["url_indietro"], 
            'disabled' => false,
        ));
    }
}
