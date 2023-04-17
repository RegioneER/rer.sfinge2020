<?php

namespace AttuazioneControlloBundle\Form\Controlli;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ValutazioneChecklistControlloType extends CommonType {

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('valutazioni_elementi', self::collection, array(
			'entry_type' => "AttuazioneControlloBundle\Form\Controlli\ValutazioneElementoChecklistControlloType",
			'allow_add' => false,
			"label" => false,
			"entry_options" => array()
		));
		
		$builder->add('pulsanti', 'IstruttorieBundle\Form\IstruttoriaButtonsType', array("url" => $options["url_indietro"], "label" => false, "disabled" => false, "invalida" => $options["invalida"], "valida" => $options["valida"]));		
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Entity\Controlli\ValutazioneChecklistControllo',
			'invalida' => false,
            'valida' => true
		));
				
		$resolver->setRequired("url_indietro");		
	}

}
