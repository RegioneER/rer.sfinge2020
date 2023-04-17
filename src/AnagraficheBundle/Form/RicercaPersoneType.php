<?php

namespace AnagraficheBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RicercaPersoneType extends AbstractType {

	public function buildForm(FormBuilderInterface $builder, array $options) {

		parent::buildForm($builder, $options);
		$builder->add('nome', 'Symfony\Component\Form\Extension\Core\Type\TextType', array('required' => false, 'label' => 'Nome'));
		$builder->add('cognome', 'Symfony\Component\Form\Extension\Core\Type\TextType', array('required' => false, 'label' => 'Cognome'));
		$builder->add('codice_fiscale', 'Symfony\Component\Form\Extension\Core\Type\TextType', array('required' => false, 'label' => 'Codice fiscale'));

	}
	
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AnagraficheBundle\Form\Entity\RicercaPersone',
		));
	}

}
