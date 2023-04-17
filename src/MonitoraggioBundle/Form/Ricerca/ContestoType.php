<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Form\Ricerca;

use Symfony\Component\Form\FormBuilderInterface;

class ContestoType extends BaseType {
     
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        $builder->add('codice', self::text, array(
            'required' => false,
            'label' => 'Codice',
        ));
        
        $builder->add('descrizione', self::text, array(
            'required' => false,
            'label' => 'Descrizione',
        ));
    }
}