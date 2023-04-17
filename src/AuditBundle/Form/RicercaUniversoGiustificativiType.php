<?php

namespace AuditBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RicercaUniversoGiustificativiType extends CommonType {

	public function buildForm(FormBuilderInterface $builder, array $options) {
		
		$builder->add('certificazione', self::entity, array(
			'class' => 'CertificazioniBundle\Entity\Certificazione',
			'expanded' => false,
			'multiple' => true,
			'required' => false,
			'label' => 'Certificazioni'
		));
	}
	
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AuditBundle\Form\Entity\RicercaUniversoGiustificativi',
		));
	}

}
