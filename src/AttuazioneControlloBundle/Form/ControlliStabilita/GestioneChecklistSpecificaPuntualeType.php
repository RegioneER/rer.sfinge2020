<?php

namespace AttuazioneControlloBundle\Form\ControlliStabilita;

use AttuazioneControlloBundle\Form\Entity\GestioneChecklistSpecifica;
use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GestioneChecklistSpecificaPuntualeType extends CommonType
{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('elementi', self::collection,[
            'entry_type' => ChecklistSpecificaPuntualeType::class,
            'label' => false,
        ]);
        $builder->add('submit',self::salva, [
            'label' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => GestioneChecklistSpecifica::class,
            'label' => false,
        ]);
    }
}