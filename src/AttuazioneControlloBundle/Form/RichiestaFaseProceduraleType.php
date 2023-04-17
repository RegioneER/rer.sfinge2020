<?php

namespace AttuazioneControlloBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use RichiesteBundle\Entity\Richiesta;
use AttuazioneControlloBundle\Entity\IterProgetto;

class RichiestaFaseProceduraleType extends CommonType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        /** @var Richiesta $richiesta */
        $richiesta = $builder->getData();

        $builder->add('mon_iter_progetti', self::collection, [
            'entry_type' => IterProgettoType::class,
            'allow_delete' => false,
            'allow_add' => false,
            'error_bubbling' => true,
            'prototype' => false,
            'entry_options' => [
                'to_beneficiario' => $options['to_beneficiario'],
                'validation_groups' => ['rendicontazione_beneficiario'],
                'empty_data' =>   new IterProgetto($richiesta),
                
            ],
            'constraints' => [
                new Valid([
                    'traverse' => TRUE,
                ]),
            ],
        ]);

        $builder->add('submit', self::salva_indietro, [
            'mostra_indietro' => true,
            'url' => $options['url_indietro'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        $resolver->setDefaults(['data_class' => Richiesta::class, 'to_beneficiario' => true]);
        $resolver->setRequired('url_indietro');
		
    }
}
