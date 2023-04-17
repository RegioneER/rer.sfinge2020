<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Form;

/**
 * Description of ProceduraAggiudicazioneType
 *
 * @author vbuscermi
 */
use BaseBundle\Form\CommonType;
use Symfony\Component\Form\CallbackTransformer;

class ProceduraAggiudicazioneType extends CommonType {

    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        $cig = $builder->getData()->getCig();
        $cigAssente = !is_null($cig) && $cig == '9999';

        $builder->add('id', self::text, array(
            'required' => true,
            'label' => 'codice',
            // 'disabled' => true
            'disabled' => $options['ruolo_lettura'],
        ));

        $builder->add('cig', self::text, array(
            'required' => true,
            'label' => 'CIG',
            'disabled' => $options['ruolo_lettura'],
        ));

        $builder->get('cig')
            ->addModelTransformer(new CallbackTransformer(
                function ($campo) {
                    return $campo;
                },
                function ($campo) {
                    return empty($campo) ? '9999' : $campo;
                }
            ));

        $builder->add('motivo_assenza_cig', self::entity, array(
            'required' => $cigAssente,
            'disabled' => $options['ruolo_lettura'],
            'label' => 'Motivo assenza CIG',
            'class' => 'MonitoraggioBundle\Entity\TC22MotivoAssenzaCIG',
            'placeholder' => '-',
        ));

        $builder->add('descrizione_procedura_aggiudicazione', self::textarea, array(
            'required' => $cigAssente,
            'disabled' => $options['ruolo_lettura'],
            'label' => 'Descrizione procedura di aggiudicazione',
        ));

        $builder->add('tipo_procedura_aggiudicazione', self::entity, array(
            'label' => 'Tipologia procedura aggiudicazione',
            'placeholder' => '-',
            'class' => 'MonitoraggioBundle\Entity\TC23TipoProceduraAggiudicazione',
            'required' => $cigAssente,
        ));

        $builder->add('importo_procedura_aggiudicazione', self::moneta, array(
            'label' => 'Importo posto a base della Procedura di Aggiudicazione',
            'required' => $cigAssente,
            'disabled' => $options['ruolo_lettura'],
        ));

        $builder->add('data_pubblicazione', self::birthday, array(
            'label' => 'Data di pubblicazione della procedura',
            'required' => $cigAssente,
            'disabled' => $options['ruolo_lettura'],
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
        ));

        $builder->add('importo_aggiudicato', self::moneta, array(
            'label' => 'Importo a fine procedura',
            'required' => $cigAssente,
        ));

        $builder->add('data_aggiudicazione', self::birthday, array(
            'label' => 'Data di aggiudicazione della procedura',
            'required' => $cigAssente,
            'disabled' => $options['ruolo_lettura'],
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
        ));

        $builder->add('submit', self::salva_indietro, array(
            'mostra_indietro' => true,
            'label_salva' => 'Salva',
            'url' => $options['url_indietro'],
        ));
    }

    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
            'data_class' => 'AttuazioneControlloBundle\Entity\ProceduraAggiudicazione',
        ));
        $resolver->setRequired(array(
            'url_indietro', 'ruolo_lettura'
        ));
    }

}
