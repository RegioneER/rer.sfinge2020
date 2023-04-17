<?php

namespace AttuazioneControlloBundle\Form\Istruttoria;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ValutazioneChecklistPagamentoType extends CommonType {

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('valutazioni_elementi', self::collection, array(
			'entry_type' => "AttuazioneControlloBundle\Form\Istruttoria\ValutazioneElementoChecklistPagamentoType",
			'allow_add' => false,
			"label" => false,
			"entry_options" => array()
		));
		if (($options["completa"] == false) && $options["bando_7"] == false){
			$builder->add('pulsanti', 'IstruttorieBundle\Form\IstruttoriaButtonsType', array("url" => $options["url_indietro"], "label" => false, "disabled" => false, "invalida" => $options["invalida"]));
		}
		if (($options["completa"] == false) && $options["bando_7"] == true){
			$builder->add('pulsanti', 'AttuazioneControlloBundle\Form\Bando_7\Istruttoria\ChecklistButtonsType', array("url" => $options["url_indietro"], "label" => false, "disabled" => false, "invalida" => $options["invalida"]));
		}	
		if (($options["completa"] == false) && $options["bando_8"] == true){
			$builder->add('pulsanti', 'AttuazioneControlloBundle\Form\Bando_8\Istruttoria\ChecklistButtonsType', array("url" => $options["url_indietro"], "label" => false, "disabled" => false, "invalida" => $options["invalida"]));
		}
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneChecklistPagamento',
			'invalida' => false,
			'completa' => false,
			'bando_7'  => false,
			'bando_8'  => false,
		));

		$resolver->setRequired("url_indietro");
	}

}
