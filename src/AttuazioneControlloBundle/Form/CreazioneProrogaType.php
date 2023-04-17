<?php

namespace AttuazioneControlloBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class CreazioneProrogaType extends CommonType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('firmatario', self::entity, array(
			'class' => "AnagraficheBundle\Entity\Persona",
			"label" => "Firmatario",
			'choice_label' => function ($persona) {
				return $persona->getNome() . " " . $persona->getCognome() . " ( " . $persona->getCodiceFiscale() . " )";
			},
			"choices" => $options["firmatabili"],
			'placeholder' => '-',
			'constraints' => array(new NotNull())
		));

		$builder->add('tipo_proroga', self::choice, array(
			'choices' => array('Proroga avvio progetto' => 'PROROGA_AVVIO', 'Proroga fine progetto' => 'PROROGA_FINE'),
			'choices_as_values' => true, 
			'required' => true, 
			'expanded' => false, 
			'multiple' => false, 
			'label' => 'Tipo proroga', 
			'placeholder' => '-',
			'constraints' => array(new NotNull())
		));


		$builder->add('data_avvio_progetto', self::birthday, array(
			"label" => "Data avvio progetto",
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',
		));

		$builder->add('data_fine_progetto', self::birthday, array(
			"label" => "Data fine progetto",
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',
		));

		$builder->add('motivazioni', self::textarea, array(
			"label" => "Motivazioni",
			'constraints' => array(new NotNull())
		));

		$builder->add("pulsanti", self::salva_indietro, array("url" => $options["url_indietro"]));
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Entity\Proroga'
		));
		$resolver->setRequired("firmatabili");
		//$resolver->setRequired("modalita_pagamento");
		$resolver->setRequired("url_indietro");
	}

}
