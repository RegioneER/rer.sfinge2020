<?php

namespace BaseBundle\Form;


use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class SalvaBloccaIndietroType extends CommonType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add('pulsante_indietro', self::indietro, array('label' => $options["label_indietro"], 'attr' => array('href' => $options["url"])));
        $builder->add('pulsante_submit', self::submit, array('label' => $options["label_salva"], 'disabled' => $options["disabled"]));
		$builder->add('pulsante_blocca', self::submit, array('label' => $options["label_blocca"], 'disabled' => ($options["disabled"] || $options["disabled_blocca"]) ));
		
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(
            array(
                'disabled' => false,
				'disabled_blocca' => false,
                'compound'=>true,
                'mapped' => false,
                'attr'=>array("class"=>"page-actions"),
                "label_salva" => "Salva",
                "label_indietro" => "Indietro",
				"label_blocca" => "Blocca",
                "mostra_indietro" => true
            ));

        $resolver->setRequired("url");
    }

    public function getBlockPrefix()
    {
        return 'salva_blocca_indietro';
    }

}






