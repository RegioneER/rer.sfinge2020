<?php

namespace SfingeBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SezionePianoCostoType extends CommonType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
        
        $builder->add('codice', self::text, array('required' => true, 'label' => 'Codice', 'constraints' => array(new \Symfony\Component\Validator\Constraints\NotNull())));
        $builder->add('titolo_sezione', self::text, array('required' => true, 'label' => 'Titolo', 'constraints' => array(new \Symfony\Component\Validator\Constraints\NotNull())));
             
        $builder->add('pulsanti', self::salva_indietro, array("url"=>$options["url_indietro"]));
	}
	
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'RichiesteBundle\Entity\SezionePianoCosto',
		));
		// $resolver->setRequired("em");
		$resolver->setRequired("url_indietro");
	}

}
