<?php

namespace SoggettoBundle\Form;

use BaseBundle\Form\CommonType;
use SoggettoBundle\Form\Entity\RicercaSoggetto;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RicercaAziendaType extends RicercaSoggettoType {

	public function buildForm(FormBuilderInterface $builder, array $options) {

		parent::buildForm($builder, $options);
	}
	
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'SoggettoBundle\Form\Entity\RicercaAzienda',
		));
	}

}
