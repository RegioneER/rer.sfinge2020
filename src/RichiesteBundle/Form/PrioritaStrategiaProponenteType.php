<?php

namespace RichiesteBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PrioritaStrategiaProponenteType extends PrioritaProponenteType {

	public function getName() {
		return "priorita_strategia_proponente";
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {


		parent::buildForm($builder, $options);

		if ($options['has_driver_kets'] == 1) {

			$builder->add('drivers', self::entity, array(
				'class' => 'SfingeBundle\Entity\Driver',
				'choice_label' => 'descrizione',
				'placeholder' => '-',
				'required' => $options["drivers_required"],
				'label' => 'Drivers',
				'multiple' => true,
			));

			$builder->add('kets', self::entity, array(
				'class' => 'SfingeBundle\Entity\KET',
				'choice_label' => 'descrizione',
				'placeholder' => '-',
				'required' => $options["kets_required"],
				'label' => 'Kets',
				'multiple' => true,
			));
		}

		if ($options['coerenza'] == 1) {
			$builder->add('coerenza_obiettivi', self::textarea, array(
				"label" => 'Coerenza con gli obiettivi strategici riportati nell’appendice 2 (max 2.000 caratteri)', 
				"required" => true,
				'attr' => array('style' => 'width: 500px', 'rows' => '5'))
			);
		}

		$builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"], 'disabled' => false));
	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'RichiesteBundle\Form\Entity\PrioritaStrategiaProponente',
			'readonly' => false,
			'kets_required' => false,
			'drivers_required' => false,
			'has_priorita_tecnologiche' => false,
			'has_driver_kets' => false,
			'laboratori' => false,
			'coerenza' => false,
			'request_data' => array()
		));

		$resolver->setRequired("url_indietro");
	}

}
