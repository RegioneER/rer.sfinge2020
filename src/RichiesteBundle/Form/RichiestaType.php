<?php

namespace RichiesteBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RichiestaType extends CommonType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
    }
	
	public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
			'readonly' => false,
        ));
		$resolver->setRequired("readonly");
        $resolver->setRequired("disabled");
    }

}
