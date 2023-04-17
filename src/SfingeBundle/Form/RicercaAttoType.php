<?php

namespace SfingeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RicercaAttoType extends AbstractType {

	public function buildForm(FormBuilderInterface $builder, array $options) {

		parent::buildForm($builder, $options);
		$builder->add('numero', 'Symfony\Component\Form\Extension\Core\Type\TextType', array('required' => false, 'label' => 'Numero'));
		$builder->add('titolo', 'Symfony\Component\Form\Extension\Core\Type\TextType', array('required' => false, 'label' => 'Titolo'));

	}
	
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'SfingeBundle\Form\Entity\RicercaAtto',
		));
	}

}
