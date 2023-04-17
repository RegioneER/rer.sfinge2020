<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Form;

/**
 * Description of TC6TipoAiutoType
 *
 * @author lfontana
 */
class TC6TipoAiutoType extends BaseFormType{
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        
        $builder->add('tipo_aiuto', self::text, array(
            'label' => 'Codice tipo aiuto',
            'disabled' => $options['disabled'],
            'required' => !$options['disabled'],
        ));
        
        $builder->add('descrizione_tipo_aiuto', self::text, array(
            'label' => 'Descrizione tipo aiuto',
            'disabled' => $options['disabled'],
            'required' => false,
        ));
        
        $builder->add('submit',self::salva_indietro, array(
            "url" => $options["url_indietro"], 
            'disabled' => false,
        ));
    }
}
