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
class TC129AltreClassificazioniType extends BaseFormType{

    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);

        $builder->add('cod_classificazione', self::text, array(
            'required' => !$options['disabled'],
            'label' => 'Codice classificazione',
            'disabled' => $options['disabled'],
        ));

       
        $builder->add('desc_classificazione', self::textarea, array(
            'required' => false,
            'label' => 'Descrizione classificazione',
            'disabled' => $options['disabled'],
        ));
        
        $builder->add('tipo_class', self::entity, array(
            'required' => !$options['disabled'],
            'label' => 'Tipo classificazione',
            'class' => 'MonitoraggioBundle\Entity\TC11TipoClassificazione',
            'choices' => $options['tipiClassificazione']
        ));
        
        $builder->add('cod_raggruppamento', self::text, array(
            'required' => !$options['disabled'],
            'label' => 'Codice raggruppamento',
            'disabled' => $options['disabled'],
        ));

        $builder->add('desc_raggruppamento', self::textarea, array(
            'required' => false,
            'label' => 'Descrizione raggruppamento',
            'disabled' => $options['disabled'],
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
            'tipiClassificazione',
        ));
    }
}
    