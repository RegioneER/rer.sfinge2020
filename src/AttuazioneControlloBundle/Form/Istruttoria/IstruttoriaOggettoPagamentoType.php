<?php

namespace AttuazioneControlloBundle\Form\Istruttoria;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class IstruttoriaOggettoPagamentoType  extends CommonType {
	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		
		$choises = array();
		
		if($options['nascondi_integrazione']){
			$choises = array('Completa' => 'Completa', 'Incompleta' => 'Incompleta');
		} else {
			$choises = array('Completa' => 'Completa', 'Incompleta' => 'Incompleta', 'Richiedere integrazione' => 'Integrazione');
		}
		
		$builder->add('nota_integrazione', self::textarea, array('required' => false, 'disabled' => false, 'label' => 'Note')); 

		$builder->add('stato_valutazione', self::choice, array(
			'choices' => $choises,
			'choices_as_values' => true, 
			'required' => true, 
			'expanded' => true, 
			'multiple' => false, 
			'label' => 'Stato valutazione',
			'constraints' => array(new NotNull()),
			"attr"        => array("onChange" => 'valutaStatoNote()')
			));
		
		$builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"]));		
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento',
			'nascondi_integrazione' => false,
		));
		$resolver->setRequired("url_indietro");

	}
}
