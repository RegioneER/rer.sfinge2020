<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Form;

/**
 * Description of TC3ResponsabileProceduraType
 *
 * @author lfontana
 */
class TC3ResponsabileProceduraType extends BaseFormType{
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        
        $builder->add('cod_tipo_resp_proc', self::text, array(
            'label' => 'Codice tipo responsabile procedura',
            'required' => !$options['disabled'],
        ));
        
        $builder->add('descrizione_responsabile_procedura', self::text, array(
            'label' => 'Descrizione responsabile procedura',
            'required' => false,
        ));
        
        $builder->add('submit',self::salva_indietro, array(
            "url" => $options["url_indietro"], 
            'disabled' => false,
        ));
    }

}
