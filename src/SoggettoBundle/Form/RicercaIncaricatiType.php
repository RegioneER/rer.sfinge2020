<?php

namespace SoggettoBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class RicercaIncaricatiType extends CommonType {

	public function buildForm(FormBuilderInterface $builder, array $options) {

		parent::buildForm($builder, $options);
		$builder->add('nome', self::text, array('required' => false, 'label' => 'Nome'));
		$builder->add('cognome', self::text, array('required' => false, 'label' => 'Cognome'));
		$builder->add('codice_fiscale', self::text, array('required' => false, 'label' => 'Codice fiscale'));
		$builder->add('email', self::text, array('required' => false, 'label' => 'Email'));
		$builder->add('incarico', self::entity, array(
			'class' => 'SoggettoBundle:TipoIncarico',
			'choice_label' => 'descrizione',
			'required' => false,
		));
		$builder->add('stato_incarico', self::entity, array(
			'class' => 'SoggettoBundle:StatoIncarico',
			'choice_label' => 'descrizione',
			'required' => false,
		));
		$builder->add('denominazione', self::text, array('required' => false, 'label' => 'Denominazione'));
	}
	
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'SoggettoBundle\Form\Entity\RicercaIncaricati',
		));
	}

}
