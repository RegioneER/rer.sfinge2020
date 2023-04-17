<?php

namespace SegnalazioniBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use BaseBundle\Form\CommonType;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\Constraints\File;

class MantisType extends CommonType {
	
	public function buildForm(FormBuilderInterface $builder, array $options) {
		
		$constraints = array(new Valid(), new File());
		$obbligatorio = $options["obbligatorio"];
		$url = $options["url_indietro"];
		$ripetiPassword = $options["ripeti_password"];
		
		$builder->add('username', self::text, array('required' => true, 'label' => 'Username'));
		$builder->add('numero_bando', self::text, array('required' => $obbligatorio, 'label' => 'Riferimento bando (titolo o determina)'));
		$builder->add('processo', self::choice, array(
			'choices_as_values' => true,
			'choices' => array(
				"Presentazione"=>"Presentazione",
				"Gestione"=>"Gestione",
				"Utenti"=>"Utenti"
			),
			'required'    => $obbligatorio,
			'placeholder' => '-',
			'empty_data'  => null));
		$builder->add('protocollo_progetto', self::text, array('required' => false, 'label' => 'Protocollo del Progetto'));
		$builder->add('oggetto', self::text, array('required' => true, 'label' => 'Oggetto della Segnalazione'));
		$builder->add('descrizione', self::textarea, array('required' => true, 'label' => 'Descrizione della Richiesta'));
		$builder->add('file', self::file ,array('label' =>"Carica file (Dimensione massima: 5MB)",'required' => false,'constraints' => $constraints));
		$builder->add('contatto_telefonico', self::text, array('required' => false, 'label' => 'Contatto Telefonico'));
		$builder->add('password',  'Symfony\Component\Form\Extension\Core\Type\PasswordType', array('required' => true , 'label' => 'Password Mantis'));
		if($ripetiPassword){
			$builder->add('ripeti_password',  'Symfony\Component\Form\Extension\Core\Type\PasswordType', array('required' => true , 'label' => 'Ripeti Password Mantis'));
		}
		$builder->add('pulsanti',self::salva_indietro,array("url"=>$url));
		
	}
	
	public function configureOptions(OptionsResolver $resolver) {
		parent::configureOptions($resolver);
		$resolver->setDefaults(array(
			'data_class' => 'SegnalazioniBundle\Form\Entity\Mantis',
		));
		$resolver->setRequired("url_indietro");
		$resolver->setRequired("obbligatorio");
		$resolver->setRequired("ripeti_password");
	}

	
}

