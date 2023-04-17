<?php

namespace AuditBundle\Form\Attuazione;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TestConformitaType extends CommonType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder->add('valutazione', self::choice, array(
			'choices' => array('1' => 1, '2' => 2, '3' => 3, '4' => 4),
			'choices_as_values' => true,
            'empty_value' => '',
			'required' => true,
			'expanded' => false, 
			'multiple' => false, 
			'label' => 'Giudizio'
			));


		$builder->add('note', self::textarea, array(
			'required' => true,
			'disabled' => false,
			'label' => 'Note'));

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
					'data_class' => 'AuditBundle\Entity\AuditRequisito',
					'readonly' => false,
		));

		$resolver->setRequired('url_indietro');
	}

}
