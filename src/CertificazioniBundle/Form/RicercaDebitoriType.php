<?php

namespace CertificazioniBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RicercaDebitoriType extends CommonType {

	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder->add('beneficiario', 'Symfony\Component\Form\Extension\Core\Type\TextType', array(
			'required' => false, 
			'label' => 'Soggetto'
		));
		
		$builder->add('cup', 'Symfony\Component\Form\Extension\Core\Type\TextType', array(
			'required' => false, 
			'label' => 'Codice CUP'
		));
		
	}
	
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'CertificazioniBundle\Form\Entity\RicercaDebitori',
		));
	}

}
