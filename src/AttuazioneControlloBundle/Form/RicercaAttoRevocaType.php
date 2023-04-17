<?php

namespace AttuazioneControlloBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RicercaAttoRevocaType extends CommonType {

	public function __construct() {
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder->add('numero', self::text, array('label' => 'Numero atto'));
		
		$builder->add('data_atto_da', self::birthday, array(
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',
			'label' => 'Data atto Da',
		));

		$builder->add('data_atto_a', self::birthday, array(
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',
			'label' => 'Data atto A',
		));
		
		$builder->add('descrizione', self::text, array('label' => 'Descrizione'));

	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Form\Entity\Revoche\RicercaAttoRevoca',
		));

	}

}
