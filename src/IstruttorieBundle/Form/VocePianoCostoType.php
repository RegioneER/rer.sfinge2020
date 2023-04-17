<?php

namespace IstruttorieBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Form\Extension\Core\DataTransformer\NumberToLocalizedStringTransformer;

class VocePianoCostoType extends CommonType {

	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder->add('importo_anno_' . $options['annualita'], self::importo, array("required" => false,
				"scale" => 2, "disabled" => true,
				"currency" => "EUR",
				"grouping" => true));
		
		$builder->add('istruttoria', 'IstruttorieBundle\Form\IstruttoriaVocePianoCostoType', array(
			'annualita' => $options['annualita']
			));
		
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'RichiesteBundle\Entity\VocePianoCosto',
			'constraints' => array(new Valid())
		));
		
		$resolver->setRequired("annualita");
		
	}

}
