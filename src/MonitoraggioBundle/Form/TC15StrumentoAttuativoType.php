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
class TC15StrumentoAttuativoType extends BaseFormType{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        
        $builder->add('cod_stru_att', self::text, array(
            'label' => 'Codice strumento attuativo',
            'disabled' => $options['disabled'],
            'required' => !$options['disabled'],
        ));
        
        $builder->add('desc_strumento_attuativo', self::text, array(
            'label' => 'Descrizione strumento attuativo',
            'disabled' => $options['disabled'],
            'required' => false,
        ));
        
        $builder->add('denom_resp_stru_att', self::text, array(
            'label' => 'Denominazione soggetto responsabile',
            'disabled' => $options['disabled'],
            'required' => false,
        ));
        
      
        $builder->add('data_approv_stru_att', self::birthday, array(
            'label' => 'Data approvazione',
            'disabled' => $options['disabled'],
            'required' => false,
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
        ));
        
         
        $builder->add('cod_tip_stru_att', self::text, array(
            'label' => 'Codice tipologia',
            'disabled' => $options['disabled'],
            'required' => false,
        ));
                
        $builder->add('desc_tip_stru_att', self::text, array(
            'label' => 'Descrizione tipologia',
            'disabled' => $options['disabled'],
            'required' => false,
        ));
        
        $builder->add('submit',self::salva_indietro, array(
            "url" => $options["url_indietro"], 
            'disabled' => false,
        ));
    }
}
