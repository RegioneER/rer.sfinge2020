<?php

namespace AuditBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RicercaUniversoPagamentiType extends CommonType {

	public function buildForm(FormBuilderInterface $builder, array $options) {
		
		$builder->add('asse', self::entity, array(
			'class' => 'SfingeBundle:Asse',
			'required' => false,
		));
		
		$builder->add('procedura', self::entity, array(
			'class' => 'SfingeBundle\Entity\Procedura',
			'expanded' => false,
			'multiple' => false,
			'required' => false,
			'label' => 'Procedura'
		));
        
		$builder->add('fase', self::choice, array(
			'choices' => array('Inviata alla PA' => 'PRESENTAZIONE', 'Istruttoria completata' => 'ISTRUTTORIA', 'In attuazione e controllo' => 'ATTUAZIONE', 'Con pagamenti certificati' => 'CERTIFICAZIONE'),
            'choices_as_values' => true,
			'expanded' => false,
			'multiple' => false,
			'required' => false,
			'label' => 'Fase'
		));        
        
		$builder->add('id', self::text, array('required' => false, 'label' => 'Id operazione'));			
		$builder->add('protocollo', self::text, array('required' => false, 'label' => 'Protocollo'));	
        $builder->add('titolo_progetto', self::text, array('required' => false, 'label' => 'Titolo progetto'));	
		$builder->add('denominazione', self::text, array('required' => false, 'label' => 'Denominazione soggetto'));	
		$builder->add('codice_fiscale', self::text, array('required' => false, 'label' => 'Codice fiscale soggetto'));
		$builder->add('certificazione', self::entity, array(
			'class' => 'CertificazioniBundle\Entity\Certificazione',
			'expanded' => false,
			'multiple' => true,
			'required' => false,
			'label' => 'Certificazioni'
		));
        $builder->add('totale_certificato', self::choice, array(
            'required' => false, 
            'label' => 'Totale certificato', 
            'choices' => array("> 0" => "> 0", "= 0" => "= 0", "< 0" => "< 0"),
            'choices_as_values' => true));
	}
	
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AuditBundle\Form\Entity\RicercaUniversoPagamenti',
		));
	}

}
