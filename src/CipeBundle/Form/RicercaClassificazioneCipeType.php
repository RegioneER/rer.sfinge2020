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
class RicercaClassificazioneCipeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$builder->add(
						'codice', 'Symfony\Component\Form\Extension\Core\Type\TextType', 
						array('required' => false, 'label' => 'codice')
					);
		$builder->add(
						'descrizione', 'Symfony\Component\Form\Extension\Core\Type\TextType', 
						array('required' => false, 'label' => 'descrizione')
					);
		
	return $builder;

		
		
		
    }
	
	public function mapping($currentChoiceKey) {
		if (is_null($currentChoiceKey)) { return ''; }
		return $currentChoiceKey ? '1' : '0';
	}
	
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'CipeBundle\Entity\Ricerche\RicercaClassificazioneCipe',
		));
	}
}