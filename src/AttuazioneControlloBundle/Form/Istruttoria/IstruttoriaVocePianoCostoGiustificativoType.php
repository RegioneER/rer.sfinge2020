<?php

namespace AttuazioneControlloBundle\Form\Istruttoria;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class IstruttoriaVocePianoCostoGiustificativoType extends CommonType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('importo_approvato', self::importo, [
            'required' => true,
            'constraints' => [new NotNull(['groups' => ['Default', 'Istruttoria']])]
        ]);

        if ($options['ripresentazione_spesa']) {
            $builder->add('importo_pagamento_successivo', self::importo, [
                'required' => false,
                'disabled' => false,
                'label' => false,
                'currency' => 'EUR',
                'grouping' => true,
            ]);
        }
        
        $builder->add('nota', self::textarea, [
            'label' => 'Nota',
            'required' => false
        ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AttuazioneControlloBundle\Entity\VocePianoCostoGiustificativo',
        ]);
        $resolver->setRequired('ripresentazione_spesa');
    }
}
