<?php

namespace SfingeBundle\Form;

use AttuazioneControlloBundle\Entity\ModalitaPagamento;
use AttuazioneControlloBundle\Entity\ModalitaPagamentoProcedura;
use BaseBundle\Form\CommonType;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class ModificaModalitaPagamentoProceduraType extends CommonType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('procedura', self::entity, [
            'class' => Procedura::class,
            'disabled' => true,
        ]);
        $builder->add('modalita_pagamento', self::entity, [
            'class' => ModalitaPagamento::class,
            'label' => 'Modalita di pagamento',
            'required' => true,
        ]);
        $builder->add('data_inizio_rendicontazione', self::datetime, [
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy HH:mm',
            'required' => true,
            'constraints' => [
                new NotNull(),
            ],
        ]);
        $builder->add('data_fine_rendicontazione', self::datetime, [
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy HH:mm',
            'required' => true,
            'constraints' => [
                new NotNull(),
            ],
        ]);
        $builder->add('data_invio_abilitata', self::datetime, [
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy HH:mm',
            'required' => false,
        ]);
        $builder->add('finestra_temporale', self::integer, [
            'required' => false,
        ]);
        $builder->add('percentuale_contributo', self::moneta, [
            'required' => false,
        ]);

        $builder->add('submit', self::salva_indietro, [
            'url' => $options['indietro'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => ModalitaPagamentoProcedura::class,
        ]);

        $resolver->setRequired('indietro');
    }
}
