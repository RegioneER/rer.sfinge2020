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
class TC127RisultatoAttesoType extends BaseFormType{
   public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);

        $builder->add('cod_classificazione_ra', self::text, array(
            'disabled' => $options['disabled'],
            'required' => !$options['disabled'],
            'label' => 'Codice risultato atteso',
                ));

        $builder->add('desc_classificazione_ra', self::textarea, array(
            'disabled' => $options['disabled'],
            'required' => false,
            'label' => 'Descrizione risultato atteso',
                ));

        $builder->add('cod_obiettivo_tem', self::text, array(
            'disabled' => $options['disabled'],
            'required' => false,
            'label' => 'Codice obiettivo tematico',
                ));
        
        $builder->add('desc_obiettivo_tem', self::textarea, array(
            'disabled' => $options['disabled'],
            'required' => false,
            'label' => 'Descrizione obiettivo tematico',
                ));
        
        $builder->add('origine_dato', self::text, array(
            'disabled' => $options['disabled'],
            'required' => false,
            'label' => 'Origine del dato',
        ));
      
        $builder->add('submit',self::salva_indietro, array(
            "url" => $options["url_indietro"], 
            'disabled' => false,
        ));

    }
}
