<?php

namespace AuditBundle\Form\Pianificazione;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SchedaOrganismoType extends CommonType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder->add('data_controllo', self::datetime, array(
			'required' => true,
			'disabled' => false,
			'label' => 'Data prevista attività controllo',
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy HH:mm'));

		$builder->add('luogo_controllo', self::text, array(
			'required' => true,
			'disabled' => false,
			'label' => 'Localizzazione attività controllo'));

		$builder->add('documento', self::documento_simple, array(
			"label" => false,
			'required' => false,
			"opzionale" => true
		));

		$builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"], 'disabled' => false));
	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(
				array(
					'data_class' => 'AuditBundle\Entity\AuditOrganismo',
					'readonly' => false,
		));

		$resolver->setRequired('url_indietro');
	}

}
