<?php

namespace AttuazioneControlloBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class DatiGeneraliPagamentoPPType extends CommonType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
        
		if(isset($options['tipologia']) && $options['tipologia'] != 'ANTICIPO'){
    		$builder->add('importo_richiesto', self::importo, array(
    			"label" => "Importo richiesto",
    			"currency" => "EUR",
    			"grouping" => true
    		));
		}
        
		if(isset($options['tipologia']) && $options['tipologia'] == 'ANTICIPO'){
			$builder->add('data_fideiussione', self::birthday, array(
				'widget' => 'single_text',
				'input' => 'datetime',
				'format' => 'dd/MM/yyyy',
				'required' => true,
				//'disabled' => $disabled,
				'label' => 'Data Fideiussione'
				));
		}

		$builder->add('intestatario', self::text, array(
			"label" => "Intestatario",
			'required' => false,
		));

		$builder->add('banca', self::text, array(
			"label" => "Banca",
			'required' => false,
		));

		$builder->add('agenzia', self::text, array(
			"label" => "Agenzia",
			'required' => false,
		));

		$builder->add('iban', self::text, array(
			"label" => "Iban",
			'required' => false,
		));

		$builder->add("pulsanti", self::salva_indietro, array("url" => $options["url_indietro"]));
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Entity\Pagamento',
            'validation_groups' => array("dati_generali")
		));
		$resolver->setRequired("url_indietro");
		$resolver->setRequired("tipologia");
	}

}
