<?php

namespace SoggettoBundle\Form;

use BaseBundle\Form\CommonType;
use DocumentoBundle\Entity\TipologiaDocumento;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentiIncaricoType extends CommonType {

	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder->add('file_nomina', self::documento,
			array('label' => false,"tipo"=>$options["DELEGA"],"cf_firmatario"=>$options["cf_firmatario"]));

		$builder->add('file_carta_identita', self::documento,
			array('label' => false,"tipo"=>TipologiaDocumento::CI,"cf_firmatario"=>$options["cf_firmatario"]));

		$builder->add('file_carta_identita_lr', self::documento,
			array('label' => false,"tipo"=>TipologiaDocumento::CI_LR,"cf_firmatario"=>$options["cf_firmatario"]));


		$builder->add('pulsanti', self::salva_indietro, array('url'=>$options["url_indietro"]));
	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'SoggettoBundle\Form\Entity\DocumentiIncarico',
			"DELEGA"=>"",
			"cf_firmatario" => ""
		));

		$resolver->setRequired("url_indietro");
	}

}
