<?php

namespace AuditBundle\Form\Pianificazione;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AuditCampioneOperazioneType extends CommonType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {             
        $builder->add('selezionato', self::checkbox, array(
            'label' => false, 
            'required' => true, 
            ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AuditBundle\Entity\AuditCampioneOperazione'
        ));
    }
}
