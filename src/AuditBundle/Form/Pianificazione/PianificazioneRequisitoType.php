<?php

namespace AuditBundle\Form\Pianificazione;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PianificazioneRequisitoType extends CommonType {

	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder->add('selezionato', self::checkbox, array(
			'label' => false,
			'required' => true,
		));
	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AuditBundle\Entity\AuditRequisito'
		));
	}

}
