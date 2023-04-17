<?php

namespace AttuazioneControlloBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RicercaAttoLiquidazioneType extends AbstractType {

	public function buildForm(FormBuilderInterface $builder, array $options) {

		parent::buildForm($builder, $options);
		$builder->add('numero', 'Symfony\Component\Form\Extension\Core\Type\TextType', array('required' => false, 'label' => 'Numero'));
		$builder->add('descrizione', 'Symfony\Component\Form\Extension\Core\Type\TextType', array('required' => false, 'label' => 'Descrizione'));
		$builder->add('asse', \Symfony\Bridge\Doctrine\Form\Type\EntityType::class, array(
			'class' => \SfingeBundle\Entity\Asse::class,
			'placeholder' => '-',
			'required' => false,
			'label' => 'Asse'
		));
		$builder->add('data_atto_da', 'Symfony\Component\Form\Extension\Core\Type\BirthdayType', array(
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',
			'required' => false,
			'label' => 'Data Atto Da',
		));
		$builder->add('data_atto_a', 'Symfony\Component\Form\Extension\Core\Type\BirthdayType', array(
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',
			'required' => false,
			'label' => 'Data Atto A',
		));

	}
	
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Form\Entity\RicercaAttoLiquidazione',
		));
	}

}
