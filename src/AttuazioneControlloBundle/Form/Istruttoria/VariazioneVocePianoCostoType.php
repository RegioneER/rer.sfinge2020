<?php

namespace AttuazioneControlloBundle\Form\Istruttoria;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Form\Extension\Core\DataTransformer\NumberToLocalizedStringTransformer;

class VariazioneVocePianoCostoType extends CommonType {

	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder->add('importo_variazione_anno_' . $options['annualita'], self::importo, array("required" => false,
				"scale" => 2, 
				"disabled" => true,
				"currency" => "EUR",
				"grouping" => true));
		
		$builder->add('nota_anno_' . $options['annualita'], self::textarea, array(
				'label' => 'Nota',
				'required' => false,
				"disabled" => true,
			)
		);
		
		$builder->add('importo_approvato_anno_' . $options['annualita'], self::importo, array(
				"required" => false,
				"scale" => 2, 
				"currency" => "EUR",
				"grouping" => true
		));
		
		$builder->add('nota_istruttore_anno_' . $options['annualita'], self::textarea, array(
				'label' => 'Nota istruttore',
				'required' => false,
			)
		);
		
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Entity\VariazioneVocePianoCosto',
			'constraints' => array(new Valid())
		));
		
		$resolver->setRequired("annualita");
		
	}

}
