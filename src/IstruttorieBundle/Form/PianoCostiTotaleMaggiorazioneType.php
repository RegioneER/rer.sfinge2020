<?php

namespace IstruttorieBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PianoCostiTotaleMaggiorazioneType extends CommonType {

	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder->add('maggiorazione_contributo_occupazionale', self::checkbox, array(
				"label" => "Maggiorazione contributo occupazionale",
				"required" => false,
			));

		$builder->add("submit", self::submit, array("label" => "Salva"));
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'IstruttorieBundle\Entity\IstruttoriaRichiesta',
		));

	}

}
