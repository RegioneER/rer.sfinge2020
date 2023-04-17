<?php

namespace AttuazioneControlloBundle\Form\Istruttoria;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RicercaVariazioniType extends CommonType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('procedura', self::entity, [
            'class' => 'SfingeBundle\Entity\Procedura',
            'expanded' => false,
            'multiple' => false,
            'required' => false,
            'label' => 'Procedura',
        ]);

        $builder->add('protocollo', self::text, ['required' => false, 'label' => 'Protocollo']);

        $builder->add('completata', self::choice, [
            'required' => false,
            'label' => 'Stato istruttoria',
            'placeholder' => '-',
            'choices_as_values' => true,
            'choices' => [
                'Completata' => true,
                'Non completata' => false,
            ],
        ]);

        $builder->add('denominazione', self::text, ['required' => false, 'label' => 'Denominazione soggetto']);
        $builder->add('codice_fiscale', self::text, ['required' => false, 'label' => 'Codice fiscale soggetto']);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => 'AttuazioneControlloBundle\Form\Entity\Istruttoria\RicercaVariazioni',
        ]);
    }
}
