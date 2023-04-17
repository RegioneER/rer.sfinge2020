<?php

namespace SoggettoBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BocciaIncaricoType extends CommonType {

	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder->add('nota',self::textarea,
            array("label"=>'Nota', "required"=>true, 'attr' => array('style' => 'width: 500px', 'rows' => '10')) );

		$builder->add('pulsanti', self::salva_indietro, array("url"=>$options["url_indietro"], "label_salva"=>"Avanti"));

	}
	
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			//'data_class' => 'SoggettoBundle\Entity\IncaricoPersona',
		));
		$resolver->setRequired("url_indietro");
	}

}
