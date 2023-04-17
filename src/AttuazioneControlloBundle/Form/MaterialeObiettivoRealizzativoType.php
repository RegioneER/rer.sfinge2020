<?php

namespace AttuazioneControlloBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MaterialeObiettivoRealizzativoType extends CommonType {

	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder->add('tipo_materiale', self::textarea, array(
			'label' => "tipo materiale",
			'disabled' => false
		));

		$builder->add('tipo_target', self::textarea, array(
			'label' => "Tipologia di target audience",
			'disabled' => false
		));

		$builder->add('link', self::textarea, array(
			'label' => "Link",
			'disabled' => false
		));

		$builder->add("pulsanti", self::submit, array("label" => "Salva"));
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Entity\MaterialeObiettivoRealizzativo',
		));
		$resolver->setRequired("url_indietro");
	}
}
