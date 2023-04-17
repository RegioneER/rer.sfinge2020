<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Form;

/**
 * Description of TC5TipoOperazioneType
 *
 * @author lfontana
 */
class TC5TipoOperazioneType extends BaseFormType{
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        
         $builder->add('tipo_operazione', self::text, array(
            'label' => 'Tipo operazione',
            'disabled' => $options['disabled'],
            'required' => !$options['disabled'],
        ));
        
        $builder->add('codice_natura_cup', self::text, array(
            'label' => 'Codice natura CUP',
            'disabled' => $options['disabled'],
            'required' => false,
        ));
         
        $builder->add('descrizione_natura_cup', self::text, array(
            'label' => 'Descrizione codice natura CUP',
            'disabled' => $options['disabled'],
            'required' => false,
        ));
        
        $builder->add('codice_tipologia_cup', self::text, array(
            'label' => 'Codice tipologia CUP',
            'disabled' => $options['disabled'],
            'required' => false,
        ));
        
        $builder->add('descrizione_tipologia_cup', self::text, array(
            'label' => 'Descrizione tipologia CUP',
            'disabled' => $options['disabled'],
            'required' => false,
        ));

        $builder->add('origine_dato', self::text, array(
            'label' => 'Origine del dato',
            'disabled' => $options['disabled'],
            'required' => false,
        ));
         
        $builder->add('submit',self::salva_indietro, array(
            "url" => $options["url_indietro"], 
            'disabled' => false,
        ));
    }

}
