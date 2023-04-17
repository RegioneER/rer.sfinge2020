<?php

namespace MonitoraggioBundle\Form\Ricerca;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RicercaProceduraAggiudicazioneType extends CommonType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        
        $builder->add('codice', self::text, array(
            'required' => false,
            'label' => 'codice',
        ));
        
        $builder->add('cig', self::text, array(
            'required' => false,
            'label' => 'CIG',
        ));

        $builder->add('motivo_assenza_cig', self::entity, array(
            'required' => false,
            'label' => 'Motivo assenza CIG',
            'class' => 'MonitoraggioBundle\Entity\TC22MotivoAssenzaCIG',
            'placeholder' => '-',
        ));

        $builder->add('descrizione_procedura_aggiudicazione', self::textarea, array(
            'required' => false,
            'label' => 'Descrizione procedura di aggiudicazione',
        ));

        $builder->add('tipo_procedura_aggiudicazione', self::entity, array(
            'label' => 'Tipologia procedura aggiudicazione',
            'placeholder' => '-',
            'class' => 'MonitoraggioBundle\Entity\TC23TipoProceduraAggiudicazione',
            'required' => false,
        ));

        $builder->add('importo_procedura_aggiudicazione', self::moneta, array(
            'label' => 'Importo della Procedura di Aggiudicazione',
            'required' => false,
        ));

        $builder->add('importo_aggiudicato', self::moneta, array(
            'label' => 'Importo aggiudicato',
            'required' => false,
        ));
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'MonitoraggioBundle\Form\Entity\RicercaProceduraAggiudicazione'
        ));
    }

}
