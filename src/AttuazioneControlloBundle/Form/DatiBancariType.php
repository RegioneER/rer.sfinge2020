<?php

namespace AttuazioneControlloBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class DatiBancariType extends CommonType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder->add('banca', self::text, array(
			"label" => "Banca",
			'constraints' => array(new NotNull())
		));
		
		$builder->add('agenzia', self::text, array(
			"label" => "Agenzia",
			'constraints' => array(new NotNull())
		));
		
		$builder->add('iban', self::text, array(
			"label" => "IBAN",
			'required' => false,
		));
		
		$builder->add('contoTesoreria', self::text, array(
			"label" => "Conto tesoreria",
			'required' => false
		));
		
		$builder->add('intestatario', self::text, array(
			"label" => "Intestatario",
			'constraints' => array(new NotNull())
		));

	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Entity\DatiBancari',
			'label' => false,
			'error_bubbling', false
		));

	}

}
