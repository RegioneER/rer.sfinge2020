<?php

namespace SfingeBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentoProceduraType extends CommonType {

	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder->add('descrizione', self::text, array('required' => true, 'label' => 'Descrizione'));
		
		$builder->add('documento', self::documento, array('label' => false, "tipo"=> 'ALTRO', "opzionale"=>false));

		$builder->add('pulsanti', self::salva_indietro, array("url"=>$options["url_indietro"]));

	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'SfingeBundle\Entity\DocumentoProcedura',
			'readonly' => false,
			"mostra_indietro" => true
		));

		$resolver->setRequired("url_indietro");
	}

}

?>
