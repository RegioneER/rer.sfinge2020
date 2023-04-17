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
class TC124MeccanismiErogazioneTerritorialeType extends BaseFormType{
   public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);

        $builder->add('cod_classificazione_met', self::text, array(
            'disabled' => $options['disabled'],
            'required' => !$options['disabled'],
            'label' => 'Codice meccanismo erogazione territoriale',
                ));

        $builder->add('desc_classificazione_met', self::textarea, array(
            'disabled' => $options['disabled'],
            'required' => false,
            'label' => 'Descrizione meccanismo erogazione territoriale',
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
