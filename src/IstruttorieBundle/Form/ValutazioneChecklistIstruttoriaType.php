<?php

namespace IstruttorieBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ValutazioneChecklistIstruttoriaType extends CommonType {

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('valutazioni_elementi', self::collection, array(
			'entry_type' => "IstruttorieBundle\Form\ValutazioneElementoChecklistIstruttoriaType",
			'allow_add' => false,
			"label" => false,
			"entry_options" => array()
		));
		
		$builder->add('pulsanti', 'IstruttorieBundle\Form\IstruttoriaButtonsType', array("url" => $options["url_indietro"], "label" => false, "disabled" => false, "invalida" => $options["invalida"], "integrazione" => $options["integrazione"], "url_integrazione" => $options["url_integrazione"]));		
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'IstruttorieBundle\Entity\ValutazioneChecklistIstruttoria',
			'invalida' => false,
			'integrazione' => false,
			'url_integrazione' => null
		));
				
		$resolver->setRequired("url_indietro");		
	}

}
