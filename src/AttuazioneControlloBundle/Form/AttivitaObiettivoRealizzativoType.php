<?php

namespace AttuazioneControlloBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AttivitaObiettivoRealizzativoType extends CommonType {

	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder->add('tipo_attivita', self::textarea, array(
			'label' => "tipo attività",
			'disabled' => false
		));

		$builder->add('tipo_target', self::textarea, array(
			'label' => "Tipologia di target audience",
			'disabled' => false
		));

		$builder->add('numero_contatti', self::integer, array(
			'label' => "Contatti",
			'disabled' => false
		));

		$builder->add('numero_partecipazioni', self::integer, array(
			'label' => "Partecipazioni",
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
			'data_class' => 'AttuazioneControlloBundle\Entity\AttivitaObiettivoRealizzativo',
		));
		$resolver->setRequired("url_indietro");
	}

}
