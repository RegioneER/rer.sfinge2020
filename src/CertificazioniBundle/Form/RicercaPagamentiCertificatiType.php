<?php

namespace CertificazioniBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;

class RicercaPagamentiCertificatiType extends RicercaPagamentiType {

	public function buildForm(FormBuilderInterface $builder, array $options) {
		
		$builder->add('id_operazione', 'Symfony\Component\Form\Extension\Core\Type\TextType', array(
			'required' => false, 
			'label' => 'Id operazione'
		));
				
		parent::buildForm($builder, $options);
		
	}
}
