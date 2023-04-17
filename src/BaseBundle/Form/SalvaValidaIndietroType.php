<?php

namespace BaseBundle\Form;


use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class SalvaValidaIndietroType extends CommonType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add('pulsante_indietro', self::indietro, array('label' => $options["label_indietro"], 'attr' => array('href' => $options["url"])));
        $builder->add('pulsante_submit', self::submit, array('label' => $options["label_salva"], 'disabled' => $options["disabled"]));
		$builder->add('pulsante_valida', self::submit, array('label' => $options["label_valida"], 'disabled' => ($options["disabled"] || $options["disabled_valida"]) ));
		
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(
            array(
                'disabled' => false,
				'disabled_valida' => false,
                'compound'=>true,
                'mapped' => false,
                'attr'=>array("class"=>"page-actions"),
                "label_salva" => "Salva",
                "label_indietro" => "Indietro",
				"label_valida" => "Valida",
                "mostra_indietro" => true
            ));

        $resolver->setRequired("url");
    }

    public function getBlockPrefix()
    {
        return 'salva_blocca_indietro';
    }

}






