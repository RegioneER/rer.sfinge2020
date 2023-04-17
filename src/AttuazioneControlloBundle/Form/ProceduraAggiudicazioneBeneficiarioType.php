<?php

namespace AttuazioneControlloBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AttuazioneControlloBundle\Entity\ProceduraAggiudicazione;
use MonitoraggioBundle\Entity\TC22MotivoAssenzaCIG;
use MonitoraggioBundle\Entity\TC23TipoProceduraAggiudicazione;
use Symfony\Component\Form\CallbackTransformer;

class ProceduraAggiudicazioneBeneficiarioType extends CommonType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);

        $builder->add('cig', self::text, [
            'label' => 'CIG',
        ]);

        $builder->add('motivo_assenza_cig', self::entity, [
            'label' => 'Motivo assenza CIG',
            'class' => TC22MotivoAssenzaCIG::class,
            'placeholder' => '-',
        ]);

        $builder->add('descrizione_procedura_aggiudicazione', self::textarea, [
            'label' => 'Descrizione procedura di aggiudicazione',
        ]);

        $builder->add('tipo_procedura_aggiudicazione', self::entity, [
            'label' => 'Tipologia procedura aggiudicazione',
            'placeholder' => '-',
            'class' => TC23TipoProceduraAggiudicazione::class,
        ]);

        $builder->add('importo_procedura_aggiudicazione', self::importo, [
            'label' => 'Importo posto a base della Procedura di Aggiudicazione',
        ]);

        $builder->add('data_pubblicazione', self::birthday, [
            'label' => 'Data di pubblicazione della procedura',
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
        ]);

        $builder->add('importo_aggiudicato', self::importo, [
            'label' => 'Importo a fine procedura',
        ]);

        $builder->add('data_aggiudicazione', self::birthday, [
            'label' => 'Data di aggiudicazione della procedura',
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
        ]);

        $builder->add('submit', self::salva_indietro, [
            'mostra_indietro' => true,
            'label_salva' => 'Salva',
            'url' => $options['url_indietro'],
        ]);

        $builder->addModelTransformer(new CallbackTransformer(
            // model -> view
            function(ProceduraAggiudicazione $model){
                return $model;
            },
            // view -> model
            function(ProceduraAggiudicazione $view){
                $v = clone $view;
                $v->normalizzaCampiBeneficiario();
                return $v;
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class' => ProceduraAggiudicazione::class,
            'required' => false
        ]);
        $resolver->setRequired([
            'url_indietro',
        ]);
    }
}
