<?php

namespace AttuazioneControlloBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RipartizionePagamentoBeneficiarioType extends CommonType{
	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('ripartizioni_importi_pagamento_beneficiario', self::collection, array(
			'entry_type' => "AttuazioneControlloBundle\Form\RipartizionePagamentoProponenteBeneficiarioType",
			'allow_add' => false,
			"label" => false,
			"entry_options" => array()
		));
		
		$builder->add("pulsante_submit", self::submit, array("label" => "Salva importi"));
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Entity\Pagamento',
		));	
	}
}
