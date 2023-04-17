<?php

namespace RichiesteBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FornitoreServizioType extends RichiestaType {

	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder->add('tipologia_servizio', self::entity,  array('class' => 'RichiesteBundle\Entity\TipologiaServizio',
			'choice_label' => function ($tipologia) {
		        return $tipologia->getCodice().' - '.$tipologia->getDescrizione();
		    },
			'placeholder' => '-',
			'required' => true,
			'label' => 'Tipologia',
			//'disabled' => $disabled,
			//'attr' => $attr
		));	

		$builder->add('descrizione', self::textarea, array(
			"label" => "Descrizione",
			"required" => true,
		));

		$builder->add('costo', self::importo, array(
			"label" => "Costo",
			"required" => true,
		));
		
		$builder->add('responsabile', self::text, array(
			"label" => "Responsabile della commessa per il fornitore",
			"required" => true,
		));

		$builder->add('giornate_uomo', self::text, array(
			"label" => "Numero di gg. persona previsti",
			"required" => true,
		));
		
        $builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"], 'disabled' => $options['disabled']));

	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'RichiesteBundle\Entity\FornitoreServizio',
			'readonly' => false,
			'femminile' => true,
			'url_indietro' => false
		));

		//$resolver->setRequired("url_indietro");
	}

}