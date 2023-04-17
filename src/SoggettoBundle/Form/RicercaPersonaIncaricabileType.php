<?php

namespace SoggettoBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RicercaPersonaIncaricabileType extends CommonType {


	public function getName() {
		return "ricerca_persona_incaricabile";
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder->add('nome', self::text, array(
			'required' => false,
			'label' => "Nome",
			'attr' => array(
				// 'placeholder' => 'Nome',
			),
		));

		$builder->add('cognome', self::text, array(
			'required' => true,
			'label' => "Cognome",
			'attr' => array(
				// 'placeholder' => 'Cognome (*)',
			),
		));

		$builder->add('codiceFiscale', self::text, array(
			'required' => true,
			'label' => "Codice fiscale",
			'attr' => array(
				// 'placeholder' => 'Email (*)',
			),
		));

		$builder->add('submit',self::submit, array('label' => 'Cerca', 'attr' => array(
				'class' => 'fieldClass'
			),
		));
	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'SoggettoBundle\Form\Entity\RicercaPersonaIncaricabile',

		));
	}

}

?>
