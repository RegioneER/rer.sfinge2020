<?php

namespace RichiesteBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use RichiesteBundle\Ricerche\RicercaRichiestaLatoPA;

class RicercaRichiestaLatoPAType extends RicercaRichiestaType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);

        $builder->add('id', self::integer, [
            'label' => 'ID',
            'required' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefault('data_class', RicercaRichiestaLatoPA::class);
    }
}
