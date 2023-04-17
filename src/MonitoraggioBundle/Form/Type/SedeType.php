<?php

namespace MonitoraggioBundle\Form\Type;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormInterface;

class SedeType extends CommonType 
{
    /**
     * {@inheritdoc}
     */
     public function buildForm(FormBuilderInterface $builder, array $options){
        parent::buildForm( $builder,  $options);

        $builder->add('indirizzo', 'MonitoraggioBundle\Form\Type\IndirizzoType', array(
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
            'data_class' => 'SoggettoBundle\Entity\Sede',
            'empty_data' => function (FormInterface $form) {
                 $proponente = $form->getParent()->getParent()->getData()->getOwner();
                 $sede = new \SoggettoBundle\Entity\Sede();
                 $sede->setSoggetto($proponente->getSoggetto());
                 return $sede;
            },
        ));
        $resolver->setRequired(array(
            'disabled', 'required',
        ));
     }        

}
