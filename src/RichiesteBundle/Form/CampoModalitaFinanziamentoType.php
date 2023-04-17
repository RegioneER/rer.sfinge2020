<?php

namespace RichiesteBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Form\Extension\Core\DataTransformer\NumberToLocalizedStringTransformer;

class CampoModalitaFinanziamentoType extends CommonType {

	public function buildForm(FormBuilderInterface $builder, array $options) {

		if ($options["mostra_importo_modalita_finanziamento"]) {
			$builder->add('importo', self::numero, array("required" => false,
				"label" => 'Valori assoluti', "scale" => 2,
				"rounding_mode" => NumberToLocalizedStringTransformer::ROUND_HALF_UP));
		}
		
		$builder->add('percentuale', self::numero, array("required" => false,
			"label" => '%', "scale" => $options["decimali_percentuale"],
			"rounding_mode" => NumberToLocalizedStringTransformer::ROUND_HALF_UP));
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'RichiesteBundle\Entity\VoceModalitaFinanziamento',
			'constraints' => array(new Valid()),
			'mostra_importo_modalita_finanziamento' => true,
			'decimali_percentuale' => 8
		));
	}

}
