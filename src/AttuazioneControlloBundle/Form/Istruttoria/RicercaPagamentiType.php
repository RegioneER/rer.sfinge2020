<?php

namespace AttuazioneControlloBundle\Form\Istruttoria;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RicercaPagamentiType extends CommonType {

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $utente = $builder->getData()->getUtente();

        if ($utente->isInvitalia()) {
            $builder->add('procedura', self::entity, array(
                'class' => 'SfingeBundle\Entity\Procedura',
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'label' => 'Procedura',
                'query_builder' => function(\SfingeBundle\Entity\ProceduraRepository $rep) {
                    $qb = $rep->createQueryBuilder('procedura')
                            ->where('procedura.id IN (95, 121, 132, 167)');
                    
                    return $qb;
                } 
            ));
        }elseif($utente->isOperatoreCogea()) {
            $builder->add('procedura', self::entity, array(
                'class' => 'SfingeBundle\Entity\Procedura',
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'label' => 'Procedura',
                'query_builder' => function(\SfingeBundle\Entity\ProceduraRepository $rep) {
                    $qb = $rep->createQueryBuilder('procedura')
                            ->where('procedura.id IN (2,5,58,64,67,70,72,75,77,81,83,107,110,111,112,116,128,140,142,161) ');
                    
                    return $qb;
                } 
            ));
        } else {
            $builder->add('procedura', self::entity, array(
                'class' => 'SfingeBundle\Entity\Procedura',
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'label' => 'Procedura'
            ));
        }

        $builder->add('asse', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', array(
            'class' => 'SfingeBundle:Asse',
            'required' => false,
        ));

        $builder->add('certificazione', self::entity, array(
            'class' => 'CertificazioniBundle\Entity\Certificazione',
            'choice_label' => 'getAnnoContabileNumero',
            'expanded' => false,
            'multiple' => false,
            'required' => false,
            'label' => 'Certificazione'
        ));

        $builder->add('protocollo', self::text, array('required' => false, 'label' => 'Protocollo'));

        $builder->add('id_richiesta', self::text, array('required' => false, 'label' => 'Id operazione'));
        
        $builder->add('finestraTemporale', self::choice, [
            'required' => false,
            'label' => 'Finestra Temporale',
            'choices_as_values' => true,
            'choices' => [
                'Prima' => '1',
                'Seconda' => '2',
                'Terza' => '3',
                'Quarta' => '4',
                'Quinta' => '5',
                'Sesta' => '6',
            ],
        ]);

        $builder->add('stato_istruttoria', self::choice, array(
            'required' => false,
            'label' => 'Stato istruttoria',
            'placeholder' => '-',
            'choices_as_values' => true,
            'choices' => array(
                'Completata' => 'COMPLETA', //Tutti i pagamenti che hanno un esito
                'Non completata' => 'NON COMPLETA', //Tutti i pagamenti che NON hanno un esito
            )));

        $builder->add('stato_pagamento', self::choice, array(
            'required' => false,
            'label' => 'Stato pagamento',
            'placeholder' => '-',
            'choices_as_values' => true,
            'choices' => array(
                'Ammesso' => 'AMMESSO', //Tutti i pagamenti con esito positivo
                'Non ammesso' => 'NON_AMMESSO', //Tutti i pagamenti con esito negativo
                'Al controllo' => 'AL CONTROLLO', //Pagamenti con i controlli in loco in corso
                'Certificato' => 'CERTIFICATO',
                'Integrazione da inviare' => 'INT DA INVIARE', //Pagamenti in istruttoria con una richiesta di integrazione creata ma non inviata
                'Integrato' => 'INTEGRATO', //Pagamenti in istruttoria, con una richiesta di integrazione alla quale il beneficiario ha già risposto
                'Non integrato' => 'NON INTEGRATO', //Pagamenti in istruttoria, con una richiesta di integrazione alla quale il beneficiario ha già risposto
                'In istruttoria' => 'IN ISTRUTTORIA', //Pagamenti in istruttoria senza nessuna richiesta di integrazione pendente
                'In integrazione' => 'IN INTEGRAZIONE', //Pagamenti in istruttoria con una richiesta di integrazione in attesa di risposta
                'In certificazione' => 'CERTIFICAZIONE',
                'Pagato' => 'PAGATO', //Tutti i pagamenti con mandato di pagamento ma non in certificazione
            )));

        $builder->add('esito_progetto', self::choice, array(
            'required' => false,
            'label' => 'Stato progetto',
            'placeholder' => '-',
            'choices_as_values' => true,
            'choices' => array(
                'Revocato' => 'REVOCATO',
                'Concluso' => 'CONCLUSO',
                'In attuazione' => 'IN ATTUAZIONE',
            )));

        $builder->add('denominazione', self::text, array('required' => false, 'label' => 'Denominazione soggetto'));
        $builder->add('codice_fiscale', self::text, array('required' => false, 'label' => 'Codice fiscale soggetto'));

        if(!is_null($builder->getData())) {
            $istruttori = $builder->getData()->getIstruttori();
        }
        else {
            $istruttori = array();
        }

        $builder->add('istruttore_corrente', self::choice, array(
            'choices' => $istruttori,
            'choices_as_values' => true,
            'choice_value' => function ($entity = null) {
                return $entity ? $entity->getId() : '';
            },
            'choice_label' => function ($value) {
                return $value->__toString();
            },
            'placeholder' => '-',
            'required' => false,
            'label' => 'Assegnato a',
        ));

        $builder->add('assegnato', self::choice, array(
            'choices' => array('Sì' => 1, 'No' => 0),
            'choices_as_values' => true,
            'placeholder' => '-',
            'required' => false,
        ));
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AttuazioneControlloBundle\Form\Entity\Istruttoria\RicercaPagamenti',
        ));
    }
}
