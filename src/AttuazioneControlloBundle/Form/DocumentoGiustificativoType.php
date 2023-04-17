<?php

namespace AttuazioneControlloBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;


class DocumentoGiustificativoType extends CommonType {

	public function buildForm(FormBuilderInterface $builder, array $options) {
		
		$builder->add('documentoFile', self::documento, array(
			'lista_tipi' => $options['lista_tipi'],
			'cf_firmatario' => $options['cf_firmatario'],
			'label' => false
		));
		
		$builder->add('nota', self::textarea, array(
			"label" => "Nota",
			"required" => false,
		));

		$builder->add("submit", self::salva_indietro, array('label' => 'Carica', 'url' => $options['url']));
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Entity\DocumentoGiustificativo',
		));
		$resolver->setRequired('lista_tipi');
		$resolver->setRequired('cf_firmatario');
		$resolver->setRequired('url');

	}

}
