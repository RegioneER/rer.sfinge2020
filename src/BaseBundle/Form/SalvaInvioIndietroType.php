<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 20/01/16
 * Time: 09:30
 */

namespace BaseBundle\Form;


use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;


class SalvaInvioIndietroType extends CommonType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add('pulsante_indietro', self::indietro, array('label' => $options["label_indietro"], 'attr' => array('href' => $options["url"])));
        $builder->add('pulsante_submit', self::submit, array('label' => $options["label_salva"], 'disabled' => $options["disabled"]));
		$builder->add('pulsante_invio', self::submit, array('label' => $options["label_invio"], 'disabled' => ($options["disabled"] || $options["disabled_invio"]) ));
		
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(
            array(
                'disabled' => false,
				'disabled_invio' => false,
                'compound'=>true,
                'mapped' => false,
                'attr'=>array("class"=>"page-actions"),
                "label_salva" => "Salva",
                "label_indietro" => "Indietro",
				"label_invio" => "Invio",
                "mostra_indietro" => true
            ));

        $resolver->setRequired("url");
    }

    public function getBlockPrefix()
    {
        return 'salva_invio_indietro';
    }

}






