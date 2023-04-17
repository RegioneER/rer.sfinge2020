<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Form\Ricerca;

/**
 * Description of TC12_9
 *
 * @author lfontana
 */
class TC12_10Type extends BaseType{
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        $builder->add('programma', self::entity, array(
            'class' => 'MonitoraggioBundle\Entity\TC4Programma',
            'choices' => $options['programmi'],
            'label' => 'Tipo classificazione',
            'required' => false,
        ));
    }

    public function setDefaultOptions(\Symfony\Component\OptionsResolver\OptionsResolverInterface $resolver) {
        parent::setDefaultOptions($resolver);
        $resolver->setRequired( array(
            'programmi',
        ));
    }
}
