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
 * Description of RicercaClassificazioneCipeTipologiaType
 *
 * @author gaetanoborgosano
 */
class RicercaClassificazioneCipeSottosettoreType extends RicercaClassificazioneCipeType
{
    
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder = parent::buildForm($builder, $options);
		
		$builder->add(
					'Settore', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', 
					array(
							'required'			=> false, 
							'label' =>			'Settore',
							'class'				=> 'CipeBundle\Entity\Classificazioni\CupSettore',
							'property'			=> 'codice',
							'empty_data'		=> null,
							'placeholder'		=> 'qualsiasi',
							'query_builder' => function(\Doctrine\ORM\EntityRepository $er){							
								return $er->createQueryBuilder('cs')						
								->addOrderBy('cs.codice', 'ASC');

								}
							)				

				);
		
		return $builder;
		
	}

	
	
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'CipeBundle\Entity\Ricerche\RicercaClassificazioneCipeSottosettore',
		));
	}
}