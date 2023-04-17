<?php

namespace SoggettoBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RicercaSoggettoType extends CommonType {

	public function buildForm(FormBuilderInterface $builder, array $options) {

		parent::buildForm($builder, $options);
		$builder->add('denominazione', self::text, array('required' => false, 'label' => 'Denominazione'));
		$builder->add('partita_iva', self::text, array('required' => false, 'label' => 'Partita Iva'));
		$builder->add('codice_fiscale', self::text, array('required' => false, 'label' => 'Codice fiscale'));
		
		$builder->add('data_costituzione_da', self::birthday, array(
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',
			'required' => false,
			'label' => 'Data costituzione da'));
		
		$builder->add('data_costituzione_a', self::birthday, array(
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',
			'required' => false,
			'label' => 'Data costituzione a'));
	}
	
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'SoggettoBundle\Form\Entity\RicercaSoggetto',
		));
	}

}
