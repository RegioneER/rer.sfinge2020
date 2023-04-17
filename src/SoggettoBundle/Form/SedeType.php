<?php

namespace SoggettoBundle\Form;

use BaseBundle\Form\CommonType;
use SoggettoBundle\Entity\Ateco;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SedeType extends CommonType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {

		$disabled = $options["visualizzazione"];
		$read_only = $options["visualizzazione"];

		if ($read_only == true) {
			$attr = array('readonly' => 'readonly');
		} else {
			$attr = array();
		}

		$builder->add('denominazione', self::text, array('required' => true, 'disabled' => $disabled, 'label' => 'Ragione sociale', 'attr' => $attr));

		$builder->add('numero_rea', self::text, array('required' => false, 'disabled' => $disabled, 'label' => 'Numero REA', 'attr' => $attr));

		$builder->add('indirizzo', 'BaseBundle\Form\IndirizzoType', [
            'readonly' => $read_only,
			'validation_groups' => $options["validation_groups"],
			'label' => false,
        ]);

		if ($options["pubblico"] == false) {
			$builder->add('ateco', self::entity, array('class' => 'SoggettoBundle\Entity\Ateco',
				'choice_label' => function (Ateco $ateco) {
					return $ateco->getCodice() . ' - ' . substr($ateco->getDescrizione(), 0, 89);
				},
				'placeholder' => '-',
				'required' => false,
				'label' => 'Codice Ateco',
				'disabled' => $disabled,
				'attr' => $attr,
			));

			$builder->add('ateco_secondario', self::entity, array('class' => 'SoggettoBundle\Entity\Ateco',
				'choice_label' => function (Ateco $ateco) {
					return $ateco->getCodice() . ' - ' . substr($ateco->getDescrizione(), 0, 89);
				},
				'placeholder' => '-',
				'required' => false,
				'label' => 'Codice Ateco secondario',
				'disabled' => $disabled,
				'attr' => $attr
			));
		}
		
		$builder->add('disabilitaCombo', self::hidden, array('data' => $options["visualizzazione"]));
		$builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"], 'disabled' => $disabled));
	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'SoggettoBundle\Entity\Sede',
			'visualizzazione' => false,
			"dataIndirizzo" => null,
			"pubblico" => false,
            "validation_groups" => ['Default', 'sede'],
		));

		$resolver->setRequired("url_indietro");
	}

}
