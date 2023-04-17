<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Form;

/**
 * Description of TC8GrandeProgetto
 *
 * @author lfontana
 */
class TC8GrandeProgettoType extends BaseFormType{
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        
        $builder->add('grande_progetto', self::text, array(
            'required' => ! $options['disabled'],
            'label' => 'Codice grande progetto',
            'disabled' => $options['disabled'],
        ));

        $builder->add('descrizione_grande_progetto', self::textarea, array(
            'required' => false,
            'label' => 'Descrizione grande progetto',
            'disabled' => $options['disabled'],
        ));
        $builder->add('programma',self::entity, array(
            'class' => 'MonitoraggioBundle\Entity\TC4Programma',
            'choices' => $options['programmi'],
            'label' => 'Programma',
            'required' => ! $options['disabled'], 
            'disabled' => $options['disabled'],
        ));

    
         $builder->add('submit',self::salva_indietro, array(
            "url" => $options["url_indietro"], 
            'disabled' => false,
        ));
    }
    
    public function setDefaultOptions(\Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver) {
        parent::setDefaultOptions($resolver);
        $resolver->setRequired( array(
            'programmi',
        ));
    }
}
