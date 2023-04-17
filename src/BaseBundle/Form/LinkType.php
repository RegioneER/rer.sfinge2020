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

class LinkType extends CommonType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add('link',self::generico);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(
            array(
                'disabled' => false,
                'mapped' => false,
                'label' => "Link",
            ));
        $resolver->setRequired("label");
    }

    public function getBlockPrefix()
    {
        return 'link';
    }

    public function getParent()
    {
        return "Symfony\Component\Form\Extension\Core\Type\FormType";
    }
}
