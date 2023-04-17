<?php

namespace AttuazioneControlloBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;


class DocumentoPagamentoType extends CommonType {

	public function buildForm(FormBuilderInterface $builder, array $options) {
		
		$builder->add('documentoFile', self::documento, array(
			'lista_tipi' => $options['lista_tipi'],
			'cf_firmatario' => $options['cf_firmatario'],
			'label' => false
		));
		
		$builder->add('nota', self::textarea, array(
			"label" => "Nota",
			"required" => false,
			//'constraints' => array(new NotNull())
		));

		$builder->add("submit", self::submit, array('label' => 'Carica'));
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Entity\DocumentoPagamento',
		));
		$resolver->setRequired('lista_tipi');
		$resolver->setRequired('cf_firmatario');

	}

}
