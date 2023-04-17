<?php

namespace AttuazioneControlloBundle\Form\Controlli;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class DatiControlloType extends CommonType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {

		
		$builder->add('data_inizio_controlli', self::birthday, array(
            "label" => "Data controllo in loco",
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',            
            'constraints' => array(new NotNull())
        ));	
		
		$builder->add('note', self::textarea, array(
			'disabled' => false,
			'label' => 'Note',
			'constraints' => array(new NotNull())));
		

		$builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"]));
	}

	public function configureOptions(OptionsResolver $resolver) {
		parent::configureOptions($resolver);

		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Entity\Controlli\ControlloProgetto'
		));

		$resolver->setRequired("url_indietro");
	}

}
