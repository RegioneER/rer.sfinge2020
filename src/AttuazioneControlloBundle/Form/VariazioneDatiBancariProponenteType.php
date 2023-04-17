<?php

namespace AttuazioneControlloBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use AttuazioneControlloBundle\Entity\VariazioneDatiBancariProponente;

class VariazioneDatiBancariProponenteType extends CommonType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
        ->add('intestatario', self::text)
        ->add('banca', self::text)
        ->add('agenzia', self::text)
        ->add('iban', self::text)
        ->add('contoTesoreria', self::text);
        
        $builder->add('submit', self::salva_indietro,[
            'url' => $options['indietro']
        ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => VariazioneDatiBancariProponente::class,

        ])
        ->setRequired('indietro');
    }
}
