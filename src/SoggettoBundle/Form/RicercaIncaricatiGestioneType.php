<?php

namespace SoggettoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class RicercaIncaricatiGestioneType extends RicercaIncaricatiType {

	public function buildForm(FormBuilderInterface $builder, array $options) {

		parent::buildForm($builder, $options);

		$builder->add('denominazione', 'Symfony\Component\Form\Extension\Core\Type\TextType', array('required' => false, 'label' => 'Denominazione soggetto'));
		$builder->add('codice_fiscale_soggetto', 'Symfony\Component\Form\Extension\Core\Type\TextType', array('required' => false, 'label' => 'Codice fiscale soggetto'));
		$builder->add('stato_incarico', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', array(
			'class' => 'SoggettoBundle:StatoIncarico',
			'choice_label' => 'descrizione',
			'required' => false,
		));
	}
	
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'SoggettoBundle\Form\Entity\RicercaIncaricatiGestione',
		));
	}

}
