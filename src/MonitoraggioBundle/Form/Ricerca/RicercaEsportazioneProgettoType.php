<?php

namespace MonitoraggioBundle\Form\Ricerca;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use SfingeBundle\Entity\Procedura;


class RicercaEsportazioneProgettoType extends CommonType{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);

        $builder->add('procedura', self::entity, [
            'class' => Procedura::class,
            'placeholder' => '-',
            'required' => false
        ])
        ->add('protocollo', self::text,[
            'required' => false,
        ]);
    }
}