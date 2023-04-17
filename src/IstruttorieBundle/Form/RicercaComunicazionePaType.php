<?php

namespace IstruttorieBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RicercaComunicazionePaType extends CommonType {

	public function buildForm(FormBuilderInterface $builder, array $options) {
		
		$builder->add('procedura', self::entity, array(
			'class' => 'SfingeBundle\Entity\Procedura',
			'expanded' => false,
			'multiple' => false,
			'required' => false,
			'label' => 'Procedura'
		));
		
		$builder->add('asse', self::entity, array(
			'class' => 'SfingeBundle\Entity\Asse',
			'expanded' => false,
			'multiple' => false,
			'required' => false,
			'label' => 'Asse'
		));
		
		$builder->add('soggetto', self::text, array('required' => false, 'label' => 'Soggetto mandatario'));		
		
		$builder->add('protocollo', self::text, array('required' => false, 'label' => 'Protocollo richiesta'));		
		
	}
	
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'IstruttorieBundle\Form\Entity\RicercaComunicazionePa',
		));
	}

}
