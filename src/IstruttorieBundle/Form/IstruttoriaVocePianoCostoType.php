<?php

namespace IstruttorieBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Form\Extension\Core\DataTransformer\NumberToLocalizedStringTransformer;

class IstruttoriaVocePianoCostoType extends CommonType {

	public function buildForm(FormBuilderInterface $builder, array $options) {
		
		$builder->add('taglio_anno_' . $options['annualita'], self::importo, array("required" => false,
				"scale" => 2,
				"currency" => "EUR",
				"grouping" => true));
		
		$builder->add('importo_ammissibile_anno_' . $options['annualita'], self::importo, array("required" => false, 
				"scale" => 2,
				"currency" => "EUR",
				"grouping" => true));
		
		$builder->add('nota_anno_' . $options['annualita'], self::textarea, array(
				'label' => 'Nota',
				'required' => false
			)
		);
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'IstruttorieBundle\Entity\IstruttoriaVocePianoCosto',
			'constraints' => array(new Valid())
		));
		
		$resolver->setRequired("annualita");
		
	}

}
