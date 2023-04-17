<?php

namespace BaseBundle\Form;


use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class ValidaInvalidaIndietroType extends CommonType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add('pulsante_indietro', self::indietro, array('label' => $options["label_indietro"], 'attr' => array('href' => $options["url"])));
		if($options["disabled_invalida"] == false) {
			$builder->add('pulsante_valida', self::submit, array('label' => $options["label_valida"]));
		}
		if($options["disabled_valida"] == false) {
			$builder->add('pulsante_invalida', self::submit, array('label' => $options["label_invalida"]));
		}
		
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(
            array(
                'compound'=>true,
                'mapped' => false,
                'attr'=>array("class"=>"page-actions"),
                "label_valida" => "Valida",
                "label_indietro" => "Indietro",
				"label_invalida" => "Invalida",
                "mostra_indietro" => true
            ));

        $resolver->setRequired("url");
		$resolver->setRequired("disabled_invalida");
		$resolver->setRequired("disabled_valida");
    }

    public function getBlockPrefix()
    {
        return 'valida_invalida_indietro';
    }

}






