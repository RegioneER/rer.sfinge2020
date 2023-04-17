<?php

namespace RichiesteBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class RicercaProponenteType extends CommonType {


	public function buildForm(FormBuilderInterface $builder, array $options) {

		$label = 'Denominazione';
		if ($options['data']->richiesta->getProcedura()->isBandoCentriStoriciColpitiDalSisma()) {
			$label = 'Professionista';
		}

		$bandiIrap = [118, 125];
        if (in_array($options['data']->richiesta->getProcedura()->getId(), $bandiIrap)) {
            $label = 'Nome e Cognome';
        }
		
		$builder->add('denominazione',self::text,array("label"=>$label,"required"=>false));
		$builder->add('partita_iva',self::text,array("label"=>"Partita iva","required"=>false));
		$builder->add('codice_fiscale',self::text,array("label"=>"Codice fiscale","required"=>false));
		$builder->add('submit',self::submit,array("label"=>"Cerca","attr"=>array("class"=>"pull-right")));
	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'readonly' => false,
            'data_class' => 'SoggettoBundle\Form\Entity\RicercaSoggetto'

		));
	}

}
