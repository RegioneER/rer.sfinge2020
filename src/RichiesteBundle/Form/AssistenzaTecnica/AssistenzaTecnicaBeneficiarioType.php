<?php


namespace RichiesteBundle\Form\AssistenzaTecnica;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class AssistenzaTecnicaBeneficiarioType extends CommonType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder->add('beneficiario', self::entity,  array('class' => 'SoggettoBundle\Entity\OrganismoIntermedio',
			'choice_label' => function ($ooii) {
		        return $ooii->getDenominazione();
		    },
			'constraints' => array(new NotNull()),
			'placeholder' => '-',
			'required' => true,
			'label' => 'Beneficiario',

		));	

		$builder->add("submit", self::submit, array("label" => "Salva"));
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'RichiesteBundle\Form\Entity\AssistenzaTecnica\AssistenzaTecnicaBeneficiario'
		));
	}

}
