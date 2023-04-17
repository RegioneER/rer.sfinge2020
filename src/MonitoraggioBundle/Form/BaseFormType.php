<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Form;
use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormInterface;
/**
 * Description of BaseFormType
 *
 * @author lfontana
 */
abstract class BaseFormType extends CommonType{

    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
			'data_class' => function (\Symfony\Component\Form\FormInterface $form) {
                return  $form->getConfig()->getOption("data_class");
            },
            'readonly' => false,
        ));
        $resolver->setRequired("url_indietro");
        $resolver->setRequired('disabled');
    }
}
