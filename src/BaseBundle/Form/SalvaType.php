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


class SalvaType extends CommonType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add('pulsante_submit', self::submit, array('label' => $options["label_salva"], 'disabled' => $options["disabled"]));
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(
            array(
                'disabled' => false,
                'compound'=>true,
                'mapped' => false,
                'attr'=>array("class"=>"page-actions"),
                "label_salva" => "Salva",
            ));
    }

}