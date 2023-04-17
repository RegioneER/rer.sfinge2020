<?php

namespace MonitoraggioBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use RichiesteBundle\Entity\IndicatoreOutput;
use RichiesteBundle\Entity\IndicatoreRisultato;
use MonitoraggioBundle\Form\Type\IndicatoreRisultatoType;
use RichiesteBundle\Entity\Richiesta;

class RichiestaIndicatoriType extends CommonType {
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $richiesta = $builder->getData();

        $builder
        ->add('mon_indicatore_risultato', self::collection, [
            'entry_type' => IndicatoreRisultatoType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'delete_empty' => true,
            'label' => false,
            'prototype_data' => new IndicatoreRisultato($richiesta),
        ])
        ->add('mon_indicatore_output', self::collection, [
            'label' => false,
            'entry_type' => MonitoraggioIndicatoreOutputType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'delete_empty' => true,
            'prototype_data' => new IndicatoreOutput($richiesta),
        ])
        ->add('submit', self::salva_indietro, [
            'url' => $options['url_indietro'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Richiesta::class,
        ]);
        $resolver->setRequired('url_indietro');
    }
}
