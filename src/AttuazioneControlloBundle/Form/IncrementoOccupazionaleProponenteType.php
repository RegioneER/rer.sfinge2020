<?php

namespace AttuazioneControlloBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IncrementoOccupazionaleProponenteType extends CommonType {
	
	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder->add('occupati_in_data_a', self::numero, array('required' => true, 'label' => false));
		$builder->add('occupati_in_data_b', self::numero, array('required' => true, 'label' => false));
		//$builder->add('allegato_dm_a', self::documento_simple, array("label" => false));	
		//$builder->add('allegato_dm_b', self::documento_simple, array("label" => false));
		
	}
	
	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Entity\IncrementoOccupazionale',
		));	
	}	
}
