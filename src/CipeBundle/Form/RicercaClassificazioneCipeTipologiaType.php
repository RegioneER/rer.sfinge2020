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

use CipeBundle\Form\RicercaClassificazioneCipeType;
use CipeBundle\Form\RicercaClassificazioneCipeRicNaturaType;

/**
 * Description of RicercaClassificazioneCipeTipologiaType
 *
 * @author gaetanoborgosano
 */
class RicercaClassificazioneCipeTipologiaType extends RicercaClassificazioneCipeRicNaturaType
{
    
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder = parent::buildForm($builder, $options);
		
		$builder->add(
						'formazione', 'Symfony\Component\Form\Extension\Core\Type\ChoiceType', 
						array(
								'required'			=> false, 'label' => 'formazione', 
								'choices'			=> array( 'si' => true, 'indefinito' => null),
								'choices_as_values' => true,
								'placeholder'		=> false,
								'choice_value'		=> array($this, "mapping"),
								'empty_data'		=> null
							)
					);
		
		return $builder;
		
	}

	
	
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'CipeBundle\Entity\Ricerche\RicercaClassificazioneCipeTipologia',
		));
	}
}