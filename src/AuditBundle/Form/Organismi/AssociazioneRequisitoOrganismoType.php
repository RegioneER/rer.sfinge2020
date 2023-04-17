<?php

namespace AuditBundle\Form\Organismi;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssociazioneRequisitoOrganismoType extends CommonType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('requisiti', self::entity, array(
			'class'   => "AuditBundle\Entity\Requisito",
			'label' => false,
			'required' => false,
			'expanded' => true,
			'multiple' => true,
		));

		$builder->add("pulsanti", self::salva_indietro, array("url" => $options["url_indietro"]));
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AuditBundle\Entity\Organismo'
		));

		$resolver->setRequired("url_indietro");
	}

}
