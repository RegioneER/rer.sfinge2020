<?php

namespace DocumentoBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class DocumentoTypeExtension extends AbstractTypeExtension
{
    /**
     * Returns the name of the type being extended.
     *
     * @return string The name of the type being extended
     */
    public function getExtendedType()
    {
        return 'Symfony\Component\Form\Extension\Core\Type\FileType';
    }

    /**
     * Add the estensione option
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefined(array('estensione'));
    }

    /**
     * Pass the image URL to the view
     *
     * @param FormView $view
     * @param FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if(isset($options["estensione"])){
            $estensione = $options["estensione"];
        } else {
             $estensione = null;
        }

        if(isset($options["show_div_container"])){
            $show_div_container = $options["show_div_container"];
        } else {
             $show_div_container = null;
        }
        
        $view->vars["estensione"] = $estensione;
        $view->vars["show_div"] = false;
    }

}