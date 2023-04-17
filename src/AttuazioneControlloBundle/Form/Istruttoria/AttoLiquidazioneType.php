<?php

namespace AttuazioneControlloBundle\Form\Istruttoria;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AttoLiquidazioneType extends CommonType {

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

		$builder->add('numero', self::text, array('required' => true, 'disabled' => $disabled, 'label' => 'Numero atto', 'attr' => $attr));
		$builder->add('data', self::birthday, array(
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',
			'disabled' => $disabled,
			'label' => 'Data atto',
			'attr' => $attr,
			'required' => true
		));        
		$builder->add('descrizione', self::text, array('required' => true, 'disabled' => $disabled, 'label' => 'Descrizione', 'attr' => $attr));
		 
		$builder->add('documento', self::documento, array('label' => false, 'disabled' => $disabled, "tipo"=> 'ATTO_LIQUIDAZIONE', 'attr' => $attr));
		
		$builder->add('asse', self::entity,  array(
			'class' => 'SfingeBundle\Entity\Asse',
			'placeholder' => '-',
			'required' => true,
			'label' => 'Asse',
			'disabled' => $disabled,
			'attr' => $attr,
		));		


		$builder->add('pulsanti', self::salva_indietro, array("url"=>$options["url_indietro"], 'disabled' => $disabled));

	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Entity\AttoLiquidazione',
			'readonly' => false,
			"mostra_indietro" => true
		));

		$resolver->setRequired("readonly");
		$resolver->setRequired("url_indietro");
	}

}
