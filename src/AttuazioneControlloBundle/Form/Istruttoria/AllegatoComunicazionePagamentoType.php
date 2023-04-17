<?php

namespace AttuazioneControlloBundle\Form\Istruttoria;

use AttuazioneControlloBundle\Entity\Istruttoria\AllegatoComunicazionePagamento;
use BaseBundle\Form\CommonType;
use DocumentoBundle\Entity\DocumentoFile;
use DocumentoBundle\Form\Type\DocumentoFileSimpleType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AllegatoComunicazionePagamentoType extends CommonType {
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('documento', DocumentoFileSimpleType::class, [
            'label' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => AllegatoComunicazionePagamento::class,
            'empty_data' => function(FormInterface $form){
                $doc = new DocumentoFile();
                return new AllegatoComunicazionePagamento(null, $doc);
            }
        ]);
    }
}
