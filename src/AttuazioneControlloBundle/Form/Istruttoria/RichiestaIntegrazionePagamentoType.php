<?php

namespace AttuazioneControlloBundle\Form\Istruttoria;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RichiestaIntegrazionePagamentoType extends CommonType {

	public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('integrazione_sostanziale', self::choice, array(
            'choice_value' => array($this, "mapping"), 
            'label' => false, 
            'choices'  => array('Formale' => false, 'Sostanziale' => true), 
            'choices_as_values' => true, 
            'expanded' => true, 
            'required' => true, 
            'placeholder' => false,
            'constraints' => array(new \Symfony\Component\Validator\Constraints\NotNull())));
        
		$builder->add('giustificativi', self::collection, array(
			'entry_type' => "AttuazioneControlloBundle\Form\Istruttoria\RichiestaIntegrazioneGiustificativoType",
			'allow_add' => false,
			"label" => false
		));
        
		$builder->add('documenti_pagamento', self::collection, array(
			'entry_type' => "AttuazioneControlloBundle\Form\Istruttoria\RichiestaIntegrazioneDocumentoType",
			'allow_add' => false,
			"label" => false
		));

        $builder->add('nota_integrazione', self::textarea, array('required' => false, 'label' => 'Nota'));	    
		
		$builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"], "label" => false, "disabled" => false));		
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Entity\Pagamento'
		));
				
		$resolver->setRequired("url_indietro");		
	}

}