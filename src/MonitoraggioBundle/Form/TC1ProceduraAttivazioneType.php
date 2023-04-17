<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Form;
use Symfony\Component\Form\FormBuilderInterface;
use MonitoraggioBundle\Entity\TC1ProceduraAttivazione;

/**
 * Description of TC1ProceduraAttivazioneType
 *
 * @author lfontana
 */
class TC1ProceduraAttivazioneType extends BaseFormType{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
    
        $builder->add('cod_proc_att', self::text, array(
            'required' => true,
            'label' => 'Codice procedura attivazione',
        ));
        
        $builder->add('cod_proc_att_locale', self::text, array(
            'required' => true,
            'label' => 'Codice procedura attivazione sistema locale',
        ));
        
        $builder->add('proceduraOperativa', self::entity, array(
            'required' => true,
            'label' => 'Procedura operativa',
            'class' => 'SfingeBundle\Entity\Procedura',
            'choices' => $options['procedureOperative']
        ));

        $builder->add('cod_aiuto_rna', self::text, array(
            'required' => true,
            'label' => 'Codice RNA',            
        ));
        
        $builder->add('tip_procedura_att', self::entity, array(
            'required' => true,
            'label' => 'Tipo procedura attivazione',
            'class' => 'MonitoraggioBundle\Entity\TC2TipoProceduraAttivazione',
            'choices' => $options['tipiProcedureAttivazione'],
            'choice_name' => 'tip_procedura_att',
        ));
        
        $builder->add('flag_aiuti', self::choice, array(
            'required' => true,
            'choices_as_values' => true,
            'choices' => array(
              'No' => 'N',
              'Sì' => 'S',
            ),
            'label' => 'Concessione di aiuti',
        ));
         
        $builder->add('descr_procedura_att', self::textarea, array(
            'required' => true,
            'label' => 'Descrizione della Procedura di Attivazione',
        ));
        
        $builder->add('tipo_resp_proc', self::entity, array(
            'required' => true,
            'label' => 'Tipo soggetto responsabile',
            'choices' => $options['responsabiliProcedure'],
            'class' => 'MonitoraggioBundle:TC3ResponsabileProcedura',
        ));
        
        $builder->add('denom_resp_proc', self::text, array(
            'required' => true,
            'label' => 'denominazione soggetto responsabile',
        ));
        
        $builder->add('data_avvio_procedura', self::birthday, array(
            'required' => true,
            'label' => 'Data avvio procedura',
            'widget' => 'single_text',
            'input' => 'datetime',
             'format' => 'dd/MM/yyyy',
        ));
        
        $builder->add('data_fine_procedura', self::birthday, array(
            'required' => false,
            'label' => 'Data fine procedura',
            'widget' => 'single_text',
            'input' => 'datetime',
             'format' => 'dd/MM/yyyy',
        ));
        
        $builder->add('cod_programma', self::text, array(
            'required' => true,
            'label' => 'Codice programma',
        ));
        
        $builder->add('flag_cancellazione', self::choice, array(
            'required' => false,
            'label' => 'Flag cancellazione',
            'choices_as_values' => true,
            'choices' => array(
                'Sì' => 'S',
            ),
            'placeholder' => 'No',
        ));
        
        $builder->add('flagFesr', self::checkbox, array(
            'required' => false,
            'label' => 'POR FESR',
        ));
        
        $builder->add('stato', self::choice, array(
            'required' => true,
            'label' => 'Stato',
            'choices_as_values' => true,
            'choices' => array_combine(TC1ProceduraAttivazione::getStati(),TC1ProceduraAttivazione::getStati()),
        ));
        
        $builder->add('submit',self::salva_indietro, array(
            "url" => $options["url_indietro"], 
            'disabled' => false,
        ));
    }
    
    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        $resolver->setRequired(array(
            'procedureOperative',
            'tipiProcedureAttivazione',
            'responsabiliProcedure',
        ));
    }

}
