<?php

namespace SfingeBundle\Form;

use AttuazioneControlloBundle\Entity\ModalitaPagamento;
use AttuazioneControlloBundle\Entity\ModalitaPagamentoProcedura;
use BaseBundle\Form\CommonType;
use SfingeBundle\Entity\Procedura;
use SfingeBundle\Entity\ProceduraRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class GestionePagamentiType extends CommonType {

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add('id_pagamento', self::text, [
            'required' => true,
        ]);


        $builder->add('submit', self::salva_indietro, [
            'url' => $options['indietro'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Entity\GestionePagamenti::class,
        ]);

        $resolver->setRequired('indietro');
    }

}
