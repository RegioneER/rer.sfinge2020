<?php

namespace AttuazioneControlloBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use DocumentoBundle\Form\Type\DocumentoFileSimpleType;
use BaseBundle\Form\SalvaType;
use AttuazioneControlloBundle\Entity\DocumentoImpegno;
use BaseBundle\Form\CommonType;

class DocumentoImpegnoType extends CommonType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('documento', DocumentoFileSimpleType::class,[
            'label' => false,
        ])
        ->add('submit', SalvaType::class, [
            'label_salva' => 'Aggiungi',
            'label' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => DocumentoImpegno::class,
        ]);
    }
}
