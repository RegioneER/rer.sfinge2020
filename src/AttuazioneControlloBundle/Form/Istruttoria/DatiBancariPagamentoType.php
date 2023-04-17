<?php

namespace AttuazioneControlloBundle\Form\Istruttoria;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class DatiBancariPagamentoType extends CommonType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		
		$builder->add('iban', self::text, array(
			"label" => "Iban",
			'constraints' => array(new NotNull())
		));
		
		$builder->add('intestatario', self::text, array(
			"label" => "Intestatario",
			'constraints' => array(new NotNull())
		));

		$builder->add('banca', self::text, array(
			"label" => "Banca",
			'constraints' => array(new NotNull())
		));

		$builder->add('agenzia', self::text, array(
			"label" => "Agenzia",
			'constraints' => array(new NotNull())
		));
		
		$builder->add('importo_richiesto', self::importo, array(
			"label" => "Importo richiesto",
			'constraints' => array(new NotNull()),
			"currency" => "EUR",
			"grouping" => true
		));		

		$builder->add('anno_spesa', self::text, array(
			'label' => 'Anno di spesa'
		));

	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Entity\Pagamento',
		));
		$resolver->setRequired("url_indietro");
		$resolver->setRequired("tipologia");
	}

}
