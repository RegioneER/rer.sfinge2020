<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Form;

/**
 * Description of TC122FormeFinanziamento
 *
 * @author lfontana
 */
class TC1210LineaAzioneType extends BaseFormType{
   public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);

        $builder->add('cod_classificazione_la', self::text, array(
            'required' => !$options['disabled'],
            'disabled' => $options['disabled'],
            'label' => 'Codice classificazione linea azione',
                ));
        
        $builder->add('cod_linea_azione', self::text, array(
            'disabled' => $options['disabled'],
            'required' => false,
            'label' => 'Codice linea azione',
                ));
        $builder->add('desc_linea_azione', self::textarea, array(
            'required' => false,
            'disabled' => $options['disabled'],
            'label' => 'Descrizione linea azione',
                ));

         $builder->add('cod_classificazione_ra', self::text, array(
            'disabled' => $options['disabled'],
            'required' => false,
            'label' => 'Codice risultato atteso',
                ));
        $builder->add('desc_classificazione_ra', self::textarea, array(
            'required' => false,
            'disabled' => $options['disabled'],
            'label' => 'Descrizione risultato atteso',
                ));

        $builder->add('programma', self::entity, array(
            'required' => !$options['disabled'],
            'label' => 'Programma',
            'class' => 'MonitoraggioBundle\Entity\TC4Programma',
            'choices' => $options['programmi']
        ));
        
        $builder->add('origine_dato', self::text, array(
            'required' => false,
            'disabled' => $options['disabled'],
            'label' => 'Origine del dato',
        ));
      
        $builder->add('submit',self::salva_indietro, array(
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
