<?php

namespace RichiesteBundle\Form;

use BaseBundle\Form\CommonType;
use RichiesteBundle\Entity\Richiesta;
use SoggettoBundle\Entity\IncaricoPersonaRichiesta;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ElencoIncaricoProgettoType extends CommonType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        /** @var Richiesta */
        $richiesta = $builder->getData();
        $prototype = new IncaricoPersonaRichiesta($richiesta);

        $builder->add('incarichi_richiesta', self::collection, [
            'allow_add' => true,
            'allow_delete' => true,
            'entry_type' => IncaricoProgettoType::class,
            'prototype_data' => $prototype,
            'by_reference' => false,
            // 'empty_data' => new IncaricoPersonaRichiesta($richiesta),
        ]);

        $builder->add('submit', self::salva_indietro, [
            "url" => $options["indietro"],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Richiesta::class,
        ]);
        $resolver->setRequired(["indietro"]);
    }
}
