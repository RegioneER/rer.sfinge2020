<?php

namespace AttuazioneControlloBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class DateProgettoPagamentoType extends CommonType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder->add('data_inizio_rendicontazione', self::birthday, array(
            "label" => "Data avvio progetto",
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',
			'disabled' => $options['disabilita_data_inizio'],
            'constraints' => array(new NotNull())
        ));
		
		if(!$options['is_beneficiario_saldo']){
			$builder->add('data_fine_rendicontazione', self::birthday, array(
				"label" => "Data fine rendicontazione " . ($options['is_saldo'] ? 'SALDO' : 'SAL'),
				'widget' => 'single_text',
				'input' => 'datetime',
				'format' => 'dd/MM/yyyy', 
				'disabled' => $options['is_saldo'],
				'constraints' => array(new NotNull())
			));
		}
		
		if($options['is_saldo']){
			$builder->add('data_conclusione_progetto', self::birthday, array(
				"label" => "Data conclusione di progetto" . ($options['proroga_approvata'] ? ' a seguito di proroga' : ''),
				'widget' => 'single_text',
				'input' => 'datetime',
				'format' => 'dd/MM/yyyy', 
				'disabled' => !$options['proroga_approvata'], // se c'è proroga il campo è libero, altrimenti precompilato
				'constraints' => array(new NotNull())
			));			
		}
		
		$builder->add("pulsanti", self::salva_indietro, array("url" => $options["url_indietro"]));
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class'             => 'AttuazioneControlloBundle\Entity\Pagamento',
			'disabilita_data_inizio' => true,
			'is_saldo'               => false,
			'proroga_approvata'      => false,
			'is_beneficiario_saldo'  => false,
		));
		$resolver->setRequired("url_indietro");
		$resolver->setRequired("tipologia");
		$resolver->setRequired("disabilita_data_inizio");
		$resolver->setRequired("is_saldo");
		$resolver->setRequired("proroga_approvata");
		$resolver->setRequired("is_beneficiario_saldo");
	}

}
