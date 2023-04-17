<?php

namespace AttuazioneControlloBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RicercaPersonaPagamentoType extends CommonType {

	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder->add('nome', self::text, array('required' => false, 'label' => 'Nome'));
		$builder->add('cognome', self::text, array('required' => false, 'label' => 'Cognome'));
		$builder->add('codice_fiscale', self::text, array('required' => false, 'label' => 'Codice fiscale'));
		$builder->add('submit',self::submit, array('label' => 'Cerca'));

	}
	
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Form\Entity\RicercaPersonaPagamento',
		));
	}

}
