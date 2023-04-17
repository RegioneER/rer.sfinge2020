<?php

namespace AttuazioneControlloBundle\Form\Revoche;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class RataRecuperoType extends CommonType {

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
		

		$builder->add('numero_incasso', self::text, array(
			'required' => false, 
			'disabled' => $disabled, 
			'label' => 'Numero reversale/i d\'incasso', 
			'attr' => $attr
		));

		$builder->add('data_incasso', self::birthday, array(
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',
			'disabled' => $disabled, 
			'label' => 'Data reversale/i d\'incasso',
			'attr' => $attr,
			'required' => false,
		)); 
		
		$builder->add('importo_interesse_legale', self::importo, array(
            "label" => "Interessi legali",
			'disabled' => $disabled, 
			"currency" => "EUR",
			"grouping" => true,
			'required' => false,
        ));
		
		$builder->add('importo_interesse_mora', self::importo, array(
            "label" => "Interessi di mora",
			'disabled' => $disabled, 
			"currency" => "EUR",
			"grouping" => true,
			'required' => false,
        ));
		
		$builder->add('importo_rata', self::importo, array(
            "label" => "Importo rata",
			'disabled' => $disabled, 
			"currency" => "EUR",
			"grouping" => true,
			'required' => false,
        )); 
		
		if ($options["penalita"] == true) {
			$builder->add('importo_sanzione', self::importo, array(
				"label" => "Importo sanzione",
				'disabled' => $disabled,
				"currency" => "EUR",
				"grouping" => true,
				'required' => true,
			));
		}
		
		$builder->add('pulsanti', self::salva_indietro, 
				array("url"=>$options["url_indietro"], 'disabled' => $disabled, 'mostra_indietro' => $options["mostra_indietro"]));

	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Entity\Revoche\RataRecupero',
			'readonly' => false,
			'penalita' => false,
			"mostra_indietro" => true
		));

		$resolver->setRequired("readonly");
		$resolver->setRequired("url_indietro");
	}

}
