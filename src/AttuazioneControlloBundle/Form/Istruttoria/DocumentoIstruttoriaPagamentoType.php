<?php

namespace AttuazioneControlloBundle\Form\Istruttoria;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class DocumentoIstruttoriaPagamentoType  extends CommonType {
	
	
	public function buildForm(FormBuilderInterface $builder, array $options) { 
		
		if($options["documento_caricato"] == false) {
			$builder->add('documento_file', self::documento, array(
				"label" => false,
				"lista_tipi" => $options["lista_tipi"],
				"required" => true
			));
		}

		$builder->add('data_richiesto', self::birthday, array(
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',
			'label' => 'Data richiesto',
			'required' => false,
		));

		$builder->add('data_ricevuto', self::birthday, array(
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',
			'label' => 'Data ricevuto',
			'required' => false,
		));

		$builder->add('data_scadenza', self::birthday, array(
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',
			'label' => 'Data di scadenza',
			'required' => false,
		));		
		
		$builder->add('submit', CommonType::salva_indietro , array('label' => 'Salva', 'url' => $options['url']));		
	}
		
	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		
		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Entity\Istruttoria\DocumentoIstruttoriaPagamento',
		));
		
		$resolver->setRequired("lista_tipi");
		$resolver->setRequired('url');
		$resolver->setRequired("documento_caricato");
	}	
}

