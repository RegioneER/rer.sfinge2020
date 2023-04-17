<?php

namespace AttuazioneControlloBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RicercaAttuazioneType extends CommonType {

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add('asse', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', array(
            'class' => 'SfingeBundle:Asse',
            'required' => false,
        ));

        $utente = $builder->getData()->getUtente();

        if ($utente->isValutatoreFesr()) {
            $builder->add('procedura', self::entity, array(
                'class' => 'SfingeBundle\Entity\Procedura',
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'label' => 'Procedura',
                'query_builder' => function(\SfingeBundle\Entity\ProceduraRepository $rep) {
                    $qb = $rep->createQueryBuilder('procedura')
                            ->where('procedura.id IN (4, 27, 7, 8, 6, 5, 58, 107, 3, 96, 24, 62, 28, 104, 69, 111, 64, 67, 72, 77, 2, 70, 83, 110, 161, 75, 79, 112, 116, 128, 142)');

                    return $qb;
                }
            ));
        } elseif ($utente->isInvitalia()) {
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
        } elseif ($utente->isConsulenteFesr()) {
            $builder->add('procedura', self::entity, array(
                'class' => 'SfingeBundle\Entity\Procedura',
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'label' => 'Procedura',
                'query_builder' => function(\SfingeBundle\Entity\ProceduraRepository $rep) {
                    $qb = $rep->createQueryBuilder('procedura')
                            ->where('procedura.id IN (4, 5, 26, 27, 28, 58)');
                    return $qb;
                }
            ));
        } elseif ($utente->isOperatoreCogea()) {
            $builder->add('procedura', self::entity, array(
                'class' => 'SfingeBundle\Entity\Procedura',
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'label' => 'Procedura',
                'query_builder' => function(\SfingeBundle\Entity\ProceduraRepository $rep) {
                    $qb = $rep->createQueryBuilder('procedura')
                            ->where('procedura.id IN (2,5,58,64,67,70,72,75,77,81,83,107,110,111,112,116,128,140,142,161)');
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

        $builder->add('modalita_pagamento', self::choice, [
            'required' => false,
            'label' => 'Stato progetto',
            'choices_as_values' => true,
            'choices' => [
                'Nessun pagamento' => 'NO_PAGAMENTO',
                'Anticipo' => 'ANTICIPO',
                'Pagamento intermedio' => 'Pagamento intermedio',
                'Saldo finale' => 'SALDO_FINALE',
                'Saldo unica soluzione' => 'UNICA_SOLUZIONE',
                '1° Sal' => 'PRIMO_SAL',
                '2° Sal' => 'SECONDO_SAL',
                '3° Sal' => 'TERZO_SAL',
                '4° Sal' => 'QUARTO_SAL',
                '5° Sal' => 'QUINTO_SAL',
                '6° Sal' => 'SESTO_SAL',
                '7° Sal' => 'SETTIMO_SAL',
                '8° Sal' => 'OTTAVO_SAL',
                '9° Sal' => 'NONO_SAL',
                '10° Sal' => 'DECIMO_SAL',
                'Trasferimento' => 'TRASFERIMENTO',
                'Revoca totale' => 'REVOCATO',
            ],
        ]);

        $builder->add('finestra_temporale', self::choice, [
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

        $builder->add('protocollo', self::text, array('required' => false, 'label' => 'Protocollo'));

        $builder->add('id', self::text, array('required' => false, 'label' => 'Id operazione'));

        $builder->add('cup', self::text, array('required' => false, 'label' => 'Cup'));

        $builder->add('denominazione', self::text, array('required' => false, 'label' => 'Denominazione soggetto'));
        $builder->add('codice_fiscale', self::text, array('required' => false, 'label' => 'Codice fiscale soggetto'));
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AttuazioneControlloBundle\Form\Entity\RicercaAttuazione',
        ));
    }

}
