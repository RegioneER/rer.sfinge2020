<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Form;

/**
 * Description of TC128TipologiaInterventoFeasrType
 *
 * @author lfontana
 */
class TC128TipologiaInterventoFeasrType extends BaseFormType{

    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);

        $builder->add('cod_classificazione_ti', self::text, array(
            'required' => !$options['disabled'],
            'label' => 'Codice classificazione tipo intervento',
            'disabled' => $options['disabled'],
        ));

        $builder->add('cod_tipo_intervento', self::text, array(
            'required' => !$options['disabled'],
            'label' => 'Codice tipologia intervento',
            'disabled' => $options['disabled'],
        ));
       
        $builder->add('desc_tipo_intervento', self::textarea, array(
            'required' => false,
            'label' => 'Descrizione tipologia intervento',
            'disabled' => $options['disabled'],
        ));

        $builder->add('cod_sottomisura', self::text, array(
            'required' => !$options['disabled'],
            'label' => 'Codice della sottomisura',
            'disabled' => $options['disabled'],
        ));

        $builder->add('desc_sottomisura', self::textarea, array(
            'required' => false,
            'label' => 'Descrizione della sottomisura',
            'disabled' => $options['disabled'],
        ));
        
        $builder->add('cod_misura', self::text, array(
            'required' => !$options['disabled'],
            'label' => 'Codice della misura',
            'disabled' => $options['disabled'],
        ));
                
        $builder->add('desc_misura', self::textarea, array(
            'required' => false,
            'label' => 'Descrizione della misurae',
            'disabled' => $options['disabled'],
        ));
        
        $builder->add('cod_focus_area', self::text, array(
            'required' => !$options['disabled'],
            'label' => 'Codice focus area',
            'disabled' => $options['disabled'],
        ));
                
        $builder->add('desc_focus_area', self::textarea, array(
            'required' => false,
            'label' => 'Descrizione focus area',
            'disabled' => $options['disabled'],
        ));
        
        $builder->add('cod_priorita', self::text, array(
            'required' => !$options['disabled'],
            'label' => 'Codice della priorità',
            'disabled' => $options['disabled'],
        ));
                
        $builder->add('desc_priorita', self::textarea, array(
            'required' => false,
            'label' => 'Descrizione priorità',
            'disabled' => $options['disabled'],
        ));
        
        $builder->add('programma', self::entity, array(
            'required' => !$options['disabled'],
            'label' => 'Programma',
            'class' => 'MonitoraggioBundle\Entity\TC4Programma',
            'choices' => $options['programmi']
        ));

         $builder->add('origine_dato', self::text, array(
            'required' => false,
            'label' => 'Origine dato',
            'disabled' => $options['disabled'],
        ));
         
        $builder->add('submit', self::salva_indietro, array(
            "url" => $options["url_indietro"],
            'disabled' => false,
        ));
    }

    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        $resolver->setRequired(array(
            'programmi',
        ));
    }
}
    