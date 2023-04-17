<?php

namespace RichiesteBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RicercaBandoManifestazioneType extends CommonType {

	public function buildForm(FormBuilderInterface $builder, array $options) {

		parent::buildForm($builder, $options);
		$builder->add('stato', self::choice, array(
			'choices' => array(
				'Aperto' => 'APERTO',
				'Chiuso' => 'CHIUSO',
			),
			'choices_as_values' => true,
			'placeholder' => '-',
			'required' => false,
		));
		$builder->add('titolo', self::text, array('required' => false, 'label' => 'Titolo'));
		$builder->add('atto', self::text, array('required' => false, 'label' => 'Numero determina/delibera'));
		$builder->add('tipo', self::choice, array(
			'choices' => array(
				'Bando' => 'BANDO',
				'Manifestazione d\'interesse' => 'MANIFESTAZIONE_INTERESSE',
			),
			'choices_as_values' => true,
			'placeholder' => '-',
			'required' => false,
		));

		$builder->add('asse', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', array(
			'class' => 'SfingeBundle:Asse',
			'required' => false,
		));

	}
	
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'RichiesteBundle\Form\Entity\RicercaBandoManifestazione',
		));
	}

}
