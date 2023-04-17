<?php

namespace AttuazioneControlloBundle\Form\Istruttoria;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class VariazionePianoCostiTotaleType extends \BaseBundle\Form\CommonType {

	public function getName() {
		return "variazione_totale_piano_costo";
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder->add('contributo_ammesso', self::importo, array(
			'required' => false,
			'label' => 'Contributo ammesso',
			"currency" => "EUR",
			"grouping" => true,
		));

		$builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"], 'disabled' => false));
	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			// 'data_class' => 'AttuazioneControlloBundle\Entity\VariazioneRichiesta',
			'readonly' => false,
			'constraints' => array(new Valid()),
		));

		$resolver->setRequired("url_indietro");
	}

}
