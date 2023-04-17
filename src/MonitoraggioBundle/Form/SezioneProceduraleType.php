<?php

namespace MonitoraggioBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use RichiesteBundle\Entity\Richiesta;
use AttuazioneControlloBundle\Entity\IterProgetto;
use AttuazioneControlloBundle\Form\IterProgettoType;
use AttuazioneControlloBundle\Form\RichiestaStatoProgettoType;
use AttuazioneControlloBundle\Entity\RichiestaStatoAttuazioneProgetto;

class SezioneProceduraleType extends CommonType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        $richiesta = $builder->getData();
        $builder->add('mon_iter_progetti', self::collection, [
            'entry_type' => IterProgettoType::class,
            'allow_delete' => true,
            'allow_add' => true,
            'delete_empty' => true,
            'prototype_data' => new IterProgetto($richiesta),
        ]);

        $builder->add('mon_stato_progetti', self::collection, [
            'entry_type' => RichiestaStatoProgettoType::class,
            'allow_delete' => true,
            'allow_add' => true,
            'delete_empty' => true,
            'prototype_data' => new RichiestaStatoAttuazioneProgetto($richiesta),
        ]);

        $builder->add('submit', self::salva_indietro, [
            'mostra_indietro' => true,
            'url' => $options['url_indietro'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        $resolver->setDefault('data_class', Richiesta::class);
        $resolver->setRequired('url_indietro');
    }
}
