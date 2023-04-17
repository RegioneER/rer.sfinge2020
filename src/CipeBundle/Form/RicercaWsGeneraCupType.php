<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace CipeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


/**
 * Description of RicercaWsGeneraCupType
 *
 * @author gaetanoborgosano
 */
class RicercaWsGeneraCupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$builder->add(
						'idRichiesta', 'Symfony\Component\Form\Extension\Core\Type\IntegerType', 
						array('required' => false, 'label' => 'Id-Richiesta')
					);
		$builder->add(
						'idProgetto', 'Symfony\Component\Form\Extension\Core\Type\IntegerType', 
						array('required' => false, 'label' => 'Id-Progetto')
					);
		$builder->add(
						'richiestaInoltrata', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', 
						array(
								'required'			=> false, 'label' => 'Richiesta inoltrata', 
								'choices'			=> array( 'si' => true, 'no' => false, 'qualsiasi' => null),
								'choices_as_values' => true,
								'placeholder'		=> false,
								'choice_value'		=> array($this, "mapping"),
								'empty_data'		=> null
							)
					);


		$builder->add(
						'esito', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', 
						array(
								'required'				=> false, 'label' => 'Esito', 
								'choices'				=> array( 'positivo' => true, 'negativo' => false, 'qualsiasi' => null),
								'choices_as_values'		=> true,
								'placeholder'			=> false,
								'choice_value'			=> array($this, "mapping"),
								'empty_data'			=> null
							)
					);
		$builder->add(
						'richiestaValida', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', 
						array(
								'required'			=> false, 'label' => 'Richiesta valida', 
								'choices'			=> array( 'si' => true, 'no' => false, 'qualsiasi' => null),
								'choices_as_values' => true,
								'placeholder'		=> false,
								'choice_value'		=> array($this, "mapping"),
								'empty_data'		=> null
							)
					);
		$builder->add(
						'curlError', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', 
						array(
								'required'			=> false, 'label' => 'Errori curl', 
								'choices'			=> array( 'si' => true, 'no' => false, 'qualsiasi' => null),
								'choices_as_values' => true,
								'placeholder'		=> false,
								'choice_value'		=> array($this, "mapping"),
								'empty_data'		=> null
							)
					);

	return $builder;

		
		
		
    }
	
	public function mapping($currentChoiceKey) {
		if (is_null($currentChoiceKey)) { return ''; }
		return $currentChoiceKey ? '1' : '0';
	}
	
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'CipeBundle\Entity\Ricerche\RicercaWsGeneraCup',
		));
	}
}