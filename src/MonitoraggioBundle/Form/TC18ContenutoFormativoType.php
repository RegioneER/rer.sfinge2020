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
class TC18ContenutoFormativoType extends BaseFormType{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        
        $builder->add('cod_contenuto_formativo', self::text, array(
            'label' => 'Codice contenuto formativo',
            'disabled' => $options['disabled'],
            'required' => !$options['disabled'],
        ));
        
        $builder->add('descrizione_contenuto_formativo', self::text, array(
            'label' => 'Descrizione contenuto formativo',
            'disabled' => $options['disabled'],
            'required' => false,
        ));
        
        $builder->add('codice_settore', self::text, array(
            'label' => 'Codice settore',
            'disabled' => $options['disabled'],
            'required' => false,
        ));
                
        $builder->add('descrizione_settore', self::text, array(
            'label' => 'Descrizione settore',
            'disabled' => $options['disabled'],
            'required' => false,
        ));
        
        $builder->add('submit',self::salva_indietro, array(
            "url" => $options["url_indietro"], 
            'disabled' => false,
        ));
    }
}
