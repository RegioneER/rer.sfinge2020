<?php

namespace AttuazioneControlloBundle\Form\Istruttoria;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MandatoPagamentoType extends CommonType {

	public function __construct() {
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$read_only = $options["readonly"];
		$disabled = $options["readonly"];

		if ($read_only == true) {
			$attr = array('readonly' => 'readonly');
		} else {
			$attr = array();
		}

        
        $builder->add('numero_mandato', self::text, array('required' => true, 'disabled' => $disabled, 'label' => 'Numero mandato', 'attr' => $attr));
	
        $builder->add('data_mandato', self::birthday, array(
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',
			'disabled' => $disabled,
			'label' => 'Data mandato',
			'attr' => $attr,
			'required' => true
		));
              
        $builder->add('importo_pagato', self::importo, array(
            "label" => "Importo pagato",
			"currency" => "EUR",
			"grouping" => true,
            'disabled' => $disabled,
            'attr' => $attr,
            'required' => true
        )); 		
       
		$builder->add('quota_fesr', self::importo, array(
            "label" => "Quota Fesr (€)",
			"currency" => "EUR",
			"grouping" => true,
            'disabled' => $disabled,
            'attr' => $attr,
            'required' => true
        ));
        
        $builder->add('quota_stato', self::importo, array(
            "label" => "Quota Stato (€)",
			"currency" => "EUR",
			"grouping" => true,
            'disabled' => $disabled,
            'attr' => $attr,
            'required' => true
        ));

        $builder->add('quota_regione', self::importo, array(
            "label" => "Quota regione (€)",
			"currency" => "EUR",
			"grouping" => true,
            'disabled' => $disabled,
            'attr' => $attr,
            'required' => true
        ));


		$builder->add('note', self::textarea, array('required' => false, 'disabled' => $disabled, 'label' => 'Note', 'attr' => $attr));      
        	
		$builder->add('atto_liquidazione', self::entity,  array(
			'class' => 'AttuazioneControlloBundle\Entity\AttoLiquidazione',
			'placeholder' => '-',
			'required' => true,
			'label' => 'Atto liquidazione',
			'disabled' => $disabled,
			'attr' => $attr,
		));		
		
		$builder->add('pulsanti', self::salva_indietro, array("url"=>$options["url_indietro"], 'disabled' => $disabled));

	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Entity\MandatoPagamento',
			'readonly' => false,
			"mostra_indietro" => true
		));

		$resolver->setRequired("readonly");
		$resolver->setRequired("url_indietro");
	}

}
