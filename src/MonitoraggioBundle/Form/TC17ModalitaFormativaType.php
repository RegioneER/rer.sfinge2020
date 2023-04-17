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
class TC17ModalitaFormativaType extends BaseFormType{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        
        $builder->add('cod_modalita_formativa', self::text, array(
            'label' => 'Codice modalità formativa',
            'disabled' => $options['disabled'],
            'required' => !$options['disabled'],
        ));
        
        $builder->add('descrizione_modalita_formativa_sottoclasse', self::text, array(
            'label' => 'Descrizione sottoclasse modalità formativa',
            'disabled' => $options['disabled'],
            'required' => false,
        ));
        
        $builder->add('descrizione_classe', self::text, array(
            'label' => 'Descrizione classe modalità formativa',
            'disabled' => $options['disabled'],
            'required' => false,
        ));
                
        $builder->add('descrizione_macro_categoria', self::text, array(
            'label' => 'Descrizione macroclasse modalità formativa',
            'disabled' => $options['disabled'],
            'required' => false,
        ));
        
        $builder->add('submit',self::salva_indietro, array(
            "url" => $options["url_indietro"], 
            'disabled' => false,
        ));
    }
}
