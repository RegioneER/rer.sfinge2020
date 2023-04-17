<?php

namespace AttuazioneControlloBundle\Form\Istruttoria;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IstruttoriaProrogaType extends CommonType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {

		if ($options["tipo_proroga"] == 'PROROGA_AVVIO') {
			$builder->add('data_avvio_approvata', self::birthday, array(
				"label" => "Data avvio approvata",
				'widget' => 'single_text',
				'input' => 'datetime',
				'format' => 'dd/MM/yyyy',
			));
		}
		
		if ($options["tipo_proroga"] == 'PROROGA_FINE') {
			$builder->add('data_fine_approvata', self::birthday, array(
				"label" => "Data fine approvata",
				'widget' => 'single_text',
				'input' => 'datetime',
				'format' => 'dd/MM/yyyy',
			));
		}

		$builder->add('approvata', self::choice, array(
			'choice_value' => array($this, "mapping"),
			'label' => 'Esito finale',
			'choices' => array('Non ammessa' => false, 'Ammessa' => true),
			'choices_as_values' => true,
			'expanded' => true,
			'required' => true,
			'placeholder' => false,
			'constraints' => array(new \Symfony\Component\Validator\Constraints\NotNull())));

		$builder->add('nota_pa', self::textarea, array(
			"label" => "Nota istruttore",
			'required' => false,
		));

		$builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"]));
	}

	public function configureOptions(OptionsResolver $resolver) {
		parent::configureOptions($resolver);

		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Entity\Proroga'
		));

		$resolver->setRequired("url_indietro");
		$resolver->setRequired("tipo_proroga"); 
	}

}
