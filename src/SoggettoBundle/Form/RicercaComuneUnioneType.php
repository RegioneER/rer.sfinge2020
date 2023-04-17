<?php

namespace SoggettoBundle\Form;

use BaseBundle\Form\CommonType;
use SoggettoBundle\Form\Entity\RicercaSoggetto;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RicercaComuneUnioneType extends CommonType  {

	public function buildForm(FormBuilderInterface $builder, array $options) {

		parent::buildForm($builder, $options);
		$builder->add('denominazione', self::text, array('required' => false, 'label' => 'Denominazione'));
		$builder->add('partita_iva', self::text, array('required' => false, 'label' => 'Partita Iva'));
		$builder->add('codice_fiscale', self::text, array('required' => false, 'label' => 'Codice fiscale'));
	}
	
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'SoggettoBundle\Form\Entity\RicercaComuneUnione',
		));
	}

}
