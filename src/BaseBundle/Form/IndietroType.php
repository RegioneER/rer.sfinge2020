<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 20/01/16
 * Time: 10:51
 */

namespace BaseBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use BaseBundle\Form\CommonType;

class IndietroType extends CommonType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add('indietro',self::generico);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(
            array(
                'disabled' => false,
                'mapped' => false,
                'label' => "Indietro",
				'attr'=>array("class"=>"page-actions"),
            ));
        $resolver->setRequired("label");
    }

    public function getBlockPrefix()
    {
        return 'indietro';
    }

    public function getParent()
    {
        return "Symfony\Component\Form\Extension\Core\Type\FormType";
    }
}
