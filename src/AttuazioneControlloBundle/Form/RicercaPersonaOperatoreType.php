<?php

namespace AttuazioneControlloBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RicercaPersonaOperatoreType extends CommonType {

	public function buildForm(FormBuilderInterface $builder, array $options) {

		// $builder->add('nome', self::text, array('required' => false, 'label' => 'Nome'));
		//$builder->add('cognome', self::text, array('required' => false, 'label' => 'Cognome'));
		// $builder->add('email_principale', self::text, array('required' => false, 'label' => 'Email'));
        $builder->add('codice_fiscale', self::text, array('required' => true, 'label' => 'Codice Fiscale'));
		$builder->add('submit',self::submit, array('label' => 'Cerca'));

	}
	
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'RichiesteBundle\Ricerche\RicercaPersonaOperatore',
		));
	}

}
