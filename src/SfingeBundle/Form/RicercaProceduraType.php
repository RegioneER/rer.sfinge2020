<?php

namespace SfingeBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RicercaProceduraType extends CommonType {

	public function buildForm(FormBuilderInterface $builder, array $options) {

		parent::buildForm($builder, $options);
		$builder->add('titolo', self::text, array(
			'required' => false, 
		'label' => 'Titolo'
	));
		$builder->add('atto', self::text, array(
			'required' => false, 
			'label' => 'Numero determina/delibera'
		));
		$builder->add('tipo', self::choice, array(
			'choices' => array(
				'Bando' => 'BANDO',
				'Manifestazione d\'interesse' => 'MANIFESTAZIONE_INTERESSE',
				'Assistenza tecnica' => 'ASSISTENZA_TECNICA',
			),
			'choices_as_values' => true,
			'placeholder' => '-',
			'required' => false,
		));
		// TODO: Filtrare utenti solo per responsabili di bando
		
		if(!is_null($builder->getData())) {
			$responsabili = $builder->getData()->getResponsabili();
		}
		else {
			$responsabili = array();
		}
		
		$builder->add('responsabile', self::choice, array(
			'choices' => $responsabili,
			'choices_as_values' => true,
			'choice_value' => function ($entity = null) {
				return $entity ? $entity->getId() : '';
			},
			'choice_label' => function ($value) {
				return $value->__toString();
			},					
			'placeholder' => '-',
			'required' => false,
		));
		$builder->add('asse', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', array(
			'class' => 'SfingeBundle:Asse',
			'required' => false,
		));
		$builder->add('amministrazione_emittente', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', array(
			'class' => 'SfingeBundle:TipoAmministrazioneEmittente',
			'choice_label' => 'descrizione',
			'required' => false,
		));
		$builder->add('anno_programmazione', self::choice, array(
			'choices' => array(
				'2014' => '2014',
				'2015' => '2015',
				'2016' => '2016',
				'2017' => '2017',
				'2018' => '2018',
				'2019' => '2019',
				'2020' => '2020',
				'2021' => '2021',
				'2022' => '2022',
			),
			'choices_as_values' => true,
			'placeholder' => '-',
			'required' => false,
		));

	}
	
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'SfingeBundle\Form\Entity\RicercaProcedura',
		));
	}

}
