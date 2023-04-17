<?php

namespace SfingeBundle\Form;

use Symfony\Component\OptionsResolver\OptionsResolver;

class ManifestazioneInteresseType extends BandoType {
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => 'SfingeBundle\Entity\ManifestazioneInteresse',
            'readonly' => false,
            'dataAsse' => null,
            'dataObiettivoSpecifico' => null,
            'assi' => [],
            'em' => null,
        ]);
        $resolver->setRequired("url_indietro");
    }
}
