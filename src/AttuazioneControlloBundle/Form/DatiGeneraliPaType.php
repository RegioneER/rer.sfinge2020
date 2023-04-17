<?php

namespace AttuazioneControlloBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class DatiGeneraliPaType extends CommonType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		
		$builder->add('cup', self::text, array(
			"label" => "Cup",
			'constraints' => array(new NotNull())
		));

		$builder->add('data_avvio', self::birthday, array(
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',
			'required' => true,
			'label' => 'Data avvio'
			));

		$builder->add('data_termine', self::birthday, array(
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',
			'required' => true,
			'label' => 'Data termine'
			));

		$builder->add('data_termine_effettivo', self::birthday, array(
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',
			'required' => true,
			'label' => 'Data termine effettivo'
			));

		$builder->add('partenariato_pubblico_privato', self::checkbox, array(
			'label'=> "L'operazione è attuata nel quadro di un partenariato pubblico privato",
			'required' => false			
		));

		$builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"], "label_salva" => "Salva"));		
		//$builder->add("pulsanti", self::salva, array());
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta',
            //'validation_groups' => array("dati_generali")
		));
		$resolver->setRequired("url_indietro");
	}

}
