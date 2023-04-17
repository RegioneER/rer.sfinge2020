<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Form\Ricerca;

use MonitoraggioBundle\Form\Ricerca\BaseType;
use Symfony\Component\Form\FormBuilderInterface;
/**
 * Description of TC2Type
 *
 * @author lfontana
 */
class TC2Type extends BaseType{
    

    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
       $builder->add('tip_procedura_att', self::integer, array(
            'required' => false,
            'label' => 'Codice della Tipologia della Procedura di Attivazione',
        ));
        $builder->add('cod_proc_att_locale', self::text, array(
            'required' => false,
            'label' => 'Descrizione della tipologia di Procedura',
        ));
    }
    
}
