<?php

namespace AttuazioneControlloBundle\Form\ProrogaRendicontazione;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AttuazioneControlloBundle\Entity\ProrogaRendicontazione;
use Symfony\Component\Form\FormInterface;
use AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta;
use AttuazioneControlloBundle\Entity\ModalitaPagamento;

class ProrogaRendicontazioneType extends CommonType
{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('modalita_pagamento', self::entity, [
            'class' => ModalitaPagamento::class,
            'label' => 'Modalità di pagamento',
            'required' => true,
        ])
        ->add('data_inizio', self::birthday, [
            'required' => false,
            'label' => 'Data inizio rendicontazione',
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
            'placeholder' => '-'
        ])
        ->add('data_scadenza', self::birthday, [
            'required' => false,
            'label' => 'Data scadenza rendicontazione',
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
        ]);
    }
        public function configureOptions(OptionsResolver $resolver) {
            $resolver->setDefaults([
                'data_class' => ProrogaRendicontazione::class,
                'empty_data' => function (FormInterface $form) {
                    /** @var AttuazioneControlloRichiesta $atc */
                    $atc = $form->getParent()->getparent()->getData();
    
                    return new ProrogaRendicontazione($atc);
                },
            ]);
        }
}