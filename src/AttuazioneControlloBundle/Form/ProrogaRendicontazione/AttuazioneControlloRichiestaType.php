<?php

namespace AttuazioneControlloBundle\Form\ProrogaRendicontazione;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta;
use AttuazioneControlloBundle\Entity\ProrogaRendicontazione;

class AttuazioneControlloRichiestaType extends CommonType
{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        /** @var AttuazioneControlloRichiesta $atc */
        $atc = $builder->getData();
        $builder->add('proroghe_rendicontazione', self::collection, [
            'allow_add' => true,
			'allow_delete' => true,
			'entry_type' => ProrogaRendicontazioneType::class,
            'label' => false,
            'prototype_data' => new ProrogaRendicontazione($atc),
            'by_reference'=> false,
            // 'empty_data' => new ProrogaRendicontazione($atc),
        ]);
    }
        public function configureOptions(OptionsResolver $resolver) {
            $resolver->setDefaults([
                'data_class' => AttuazioneControlloRichiesta::class,
            ]);
        }

}