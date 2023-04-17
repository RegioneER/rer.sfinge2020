<?php

namespace CertificazioniBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RicercaDecertificazioniType extends CommonType {

	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder->add('asse', self::entity, array(
			'class' => 'SfingeBundle\Entity\Asse',
			// 'choices' 
			'expanded' => false,
			'multiple' => false,
			'required' => false,
			'label' => 'Asse'
		));
        
		$builder->add('procedura', self::entity, array(
			'class' => 'SfingeBundle\Entity\Procedura',
			'expanded' => false,
			'multiple' => false,
			'required' => false,
			'label' => 'Procedura'
		));
		
		$builder->add('beneficiario', 'Symfony\Component\Form\Extension\Core\Type\TextType', array(
			'required' => false, 
			'label' => 'Soggetto'
		));
		
		$builder->add('id_pagamento', 'Symfony\Component\Form\Extension\Core\Type\TextType', array(
			'required' => false, 
			'label' => 'Id pagamento'
		));
		
		$builder->add('cup', 'Symfony\Component\Form\Extension\Core\Type\TextType', array(
			'required' => false, 
			'label' => 'Codice CUP'
		));
		
		$builder->add('id_operazione', 'Symfony\Component\Form\Extension\Core\Type\TextType', array(
			'required' => false, 
			'label' => 'Id operazione'
		));
		
	}
	
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'CertificazioniBundle\Form\Entity\RicercaCompensazioni',
		));
	}

}
