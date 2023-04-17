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

/**
 * Description of RicercaClassificazioneCipeRicNaturaType
 *
 * @author gaetanoborgosano
 */
class RicercaClassificazioneCipeRicNaturaType extends RicercaClassificazioneCipeType
{
    
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder = parent::buildForm($builder, $options);
		
		$builder->add(
						'Natura', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', 
						array(
								'required'			=> false, 
								'label' => 'Natura',
								'class'				=> 'CipeBundle\Entity\Classificazioni\CupNatura',
								'property'			=> 'codice',
								'empty_data'		=> null,
								'placeholder'		=> 'qualsiasi',
								'query_builder' => function(\Doctrine\ORM\EntityRepository $er){							
									return $er->createQueryBuilder('cn')						
									->addOrderBy('cn.codice', 'ASC');
					
									}
								)				

					);
		return $builder;
		
	}

	
		
		
    
	
	public function mapping($currentChoiceKey) {
		if (is_null($currentChoiceKey)) { return ''; }
		return $currentChoiceKey ? '1' : '0';
	}
	
	
}