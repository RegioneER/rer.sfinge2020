<?php

namespace SoggettoBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AziendaType extends SoggettoType {

	public function __construct() {
                parent::__construct();
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {

		parent::buildForm($builder, $options);

		$read_only = $options["readonly"];
		$disabled = $options["readonly"];


		if ($read_only == true) {
			$attr = array('readonly' => 'readonly');
		} else {
			$attr = array();
		}


		$builder->get("dimensione_impresa")->setRequired(false);
        
		$builder->add('ccia', self::text, array('required' => false, 'disabled' => $disabled, 'label' => 'Registro CCIA di', 'attr' => $attr));

		$builder->add('data_ccia', self::birthday, array(
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',
			'required' => false,
			'disabled' => $disabled,
			'label' => 'Data iscrizione CCIA',
			'attr' => $attr));

		$builder->add('rea', self::text, array('required' => false, 'disabled' => $disabled, 'label' => 'Numero REA', 'attr' => $attr));

		$builder->add('data_rea', self::birthday, array(
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',
			'required' => false,
			'disabled' => $disabled,
			'label' => 'Data iscrizione REA',
			'attr' => $attr));

		$builder->add('registro_equivalente', self::text, array('required' => false, 'disabled' => $disabled, 'label' => 'Registro equivalente', 'attr' => $attr));
        
	}

	public function mapping($currentChoiceKey) {
		if (is_null($currentChoiceKey)) {
			return '';
		}
		return $currentChoiceKey ? '1' : '0';
	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults([
			'data_class' => 'SoggettoBundle\Entity\Azienda',
			'readonly' => false,
			'validation_groups' => function($form) {
				$data = $form->getData();
				if (is_object($data->getStato())) {
					if ($data->getStato()->getDenominazione() == 'Italia') {
						return array("Default", "impresa", "statoItalia");
					} else {
						return array("Default", "impresa");
					}
				} else {
					return array("Default", "impresa");
				}
			},
			'em' => null,
			'dataIndirizzo' => null,
		]);
		
		$resolver->setRequired("url_indietro");
		$resolver->setRequired("tipo");
	}

}
		