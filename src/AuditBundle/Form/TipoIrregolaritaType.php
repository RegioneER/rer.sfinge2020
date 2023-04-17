<?php

namespace AuditBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TipoIrregolaritaType extends CommonType {


	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {

		$read_only = $options["readonly"];
		$disabled = $options["readonly"];
		$em = $options["em"];

		if ($read_only == true) {
			$attr = array('readonly' => 'readonly');
		} else {
			$attr = array();
		}


		$builder->add('denominazione', self::text, array('required' => true, 'disabled' => $disabled, 'label' => 'Denominazione', 'attr' => $attr));

		$builder->add('pulsanti',self::salva_indietro,array("url"=>$options["url_indietro"], 'disabled' => $disabled));
	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(
			array(
				'data_class' => 'AuditBundle\Entity\TipoIrregolarita',
				'readonly' => false,
		));
		$resolver->setRequired("readonly");
		$resolver->setRequired("em");
		$resolver->setRequired("url_indietro");


	}

}
