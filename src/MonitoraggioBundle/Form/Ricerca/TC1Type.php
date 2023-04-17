<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Form\Ricerca;
use MonitoraggioBundle\Entity\TC1ProceduraAttivazione;
/**
 * Description of Tc1Type
 *
 * @author lfontana
 */
class TC1Type extends BaseType{
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        $builder->add('cod_proc_att_locale', self::text, array(
           'required' => false,
           'label'  => 'Codice procedura attivazione locale',
        ));
        
        $builder->add('tip_procedura_att', self::entity, array(
            'required' => false,
            'label' => 'Tipo procedura attivazione',
            'placeholder' => '-',
            'choices' => $options['tipiProcedura'],
            'class' => 'MonitoraggioBundle:TC2TipoProceduraAttivazione',
        ));
        
        $builder->add('descr_procedura_att', self::text,array(
            'required' => false,
            'label' => 'Descrizione procedura attivazione',
        ));
        
        $builder->add('stato', self::choice ,array(
            'required' => false,
            'label' => 'Stato',
            'choices_as_values' => true,
            'choices' => array_combine( TC1ProceduraAttivazione::$STATI, TC1ProceduraAttivazione::$STATI),
            'placeholder' => '-',
        ));
    }
    
    public function setDefaultOptions(\Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver) {
        parent::setDefaultOptions($resolver);
        $resolver->setRequired( array(
            'tipiProcedura'
        ));
    }


}
