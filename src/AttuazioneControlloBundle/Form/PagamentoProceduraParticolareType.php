<?php

namespace AttuazioneControlloBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class PagamentoProceduraParticolareType extends CommonType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder->add('modalita_pagamento', self::entity, array(
			'class' => "AttuazioneControlloBundle\Entity\ModalitaPagamento",
			"label" => "Modalità pagamento",
			"choices" => $options["modalita_pagamento"],
			'placeholder' => '-',
			'constraints' => array(new NotNull())
		));

		$builder->add("pulsanti", self::salva_indietro, array("url" => $options["url_indietro"]));
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Entity\Pagamento'
		));
		$resolver->setRequired("modalita_pagamento");
		$resolver->setRequired("url_indietro");
	}

}
