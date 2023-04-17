<?php

namespace AttuazioneControlloBundle\Form\Istruttoria;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AttuazioneControlloBundle\Entity\Istruttoria\AllegatoRichiestaChiarimento;
use DocumentoBundle\Form\Type\DocumentoFileSimpleType;
use Symfony\Component\Form\FormInterface;
use DocumentoBundle\Entity\DocumentoFile;
use DocumentoBundle\Entity\TipologiaDocumento;
use Doctrine\ORM\EntityManagerInterface;

class AllegatoRichiestaChiarimentoType extends CommonType {

	
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('documento', DocumentoFileSimpleType::class, [
			'label' => false,
		]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
			'data_class' => AllegatoRichiestaChiarimento::class,
			'empty_data' => function(FormInterface $form){
				$doc = new DocumentoFile();
				return new AllegatoRichiestaChiarimento(null, $doc);
			}
        ]);
    }
}
