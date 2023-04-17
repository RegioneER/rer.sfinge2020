<?php

namespace AuditBundle\Form\Attuazione;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class DettaglioGiustificativoType extends CommonType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('conforme', self::choice, array(
			'choice_value' => array($this, "mapping"),
			'choices' => array('Si' => true, 'No' => false),
			'placeholder' => '',
			'choices_as_values' => true,
			'label' => "Conforme",
			'required' => true,
			'constraints' => array(new NotNull())
		));

		$builder->add('spesa_irregolare_pre_contraddittorio', self::importo, array(
			"label" => "Spesa irregolare pre-contraddittorio",
			"scale" => 2,
			"currency" => "EUR",
			'required' => true,
			"grouping" => true,
			'constraints' => array(new NotNull())
		));

		$builder->add('contributo_pubblico_pre_contraddittorio', self::importo, array(
			"label" => "Contributo pubblico pre-contraddittorio",
			"scale" => 2,
			"currency" => "EUR",
			'required' => true,
			"grouping" => true,
			'constraints' => array(new NotNull())
		));

		$builder->add('spesa_irregolare_post_contraddittorio', self::importo, array(
			"label" => "Spesa irregolare post-contraddittorio",
			"scale" => 2,
			"currency" => "EUR",
			'required' => true,
			"grouping" => true,
			'constraints' => array(new NotNull())
		));
		
		$builder->add('contributo_pubblico_post_contraddittorio', self::importo, array(
			"label" => "Contributo pubblico post-contraddittorio",
			"scale" => 2,
			"currency" => "EUR",
			'required' => true,
			"grouping" => true,
			'constraints' => array(new NotNull())
		));

		$builder->add('natura_irregolarita', self::entity, array(
			'class' => 'AuditBundle\Entity\NaturaIrregolarita',
			'placeholder' => false,
			'expanded' => false,
			'multiple' => false,
			'required' => true,
			'label' => 'Natura irregolarità'
		));

		$builder->add('tipo_irregolarita', self::entity, array(
			'class' => 'AuditBundle\Entity\TipoIrregolarita',
			'placeholder' => false,
			'expanded' => false,
			'multiple' => false,
			'required' => true,
			'label' => 'Tipo irregolarità'
		));

		$builder->add('note', self::AdvancedTextType, array(
			'label' => "Note",
			'disabled' => false,
			'required' => false
		));

		$builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"]));
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AuditBundle\Entity\AuditCampioneGiustificativo'
		));

		$resolver->setRequired("url_indietro");
	}

}
