<?php

namespace NotizieBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RicercaNotizieType extends AbstractType {

	public function buildForm(FormBuilderInterface $builder, array $options) {

		parent::buildForm($builder, $options);
		$builder->add('titolo', 'Symfony\Component\Form\Extension\Core\Type\TextType', array('required' => false, 'label' => 'Titolo'));
		$builder->add('testo', 'Symfony\Component\Form\Extension\Core\Type\TextType', array('required' => false, 'label' => 'Testo'));
		$builder->add('visibilita', 'Symfony\Component\Form\Extension\Core\Type\TextType', array('required' => false, 'label' => 'Ruolo'));

	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'NotizieBundle\Form\Entity\RicercaNotiziaAdmin',
		));
	}

}
