<?php

namespace MonitoraggioBundle\Form\Type;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormInterface;

class SedeOperativaType extends CommonType{
    
    /**
     * {@inheritdoc}
     */
     public function buildForm(FormBuilderInterface $builder, array $options){
        parent::buildForm( $builder,  $options);

        $builder->add('sede', 'MonitoraggioBundle\Form\Type\SedeType', array(
            'label' => false,
            'disabled' => $options['disabled'],
            'required' => $options['required'],
        ));
     }

    /**
     * {@inheritdoc}
     */
     public function configureOptions(OptionsResolver $resolver)
     {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
            'data_class' => 'RichiesteBundle\Entity\SedeOperativa',
            'empty_data' => function (FormInterface $form) {
                 $proponente = $form->getParent()->getData()->getOwner();
                 return new \RichiesteBundle\Entity\SedeOperativa($proponente);
            },
        ));
        $resolver->setRequired(array(
            'disabled', 'required',
        ));
     }        
}
