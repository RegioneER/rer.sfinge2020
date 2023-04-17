<?php

namespace CertificazioniBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Regex;

class CertificazionePagamentoType extends CommonType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('importo', self::importo, array(
            "label" => false,
            "required" => false,
        ));
               
        $builder->add('selezionato', self::checkbox, array(
            'label' => false, 
            'required' => true, 
            ));

        $builder->add('aiuto_di_stato', self::checkbox, array(
            'label' => false, 
            'required' => false, 
            ));

        $builder->add('strumento_finanziario', self::checkbox, array(
            'label' => false, 
            'required' => false, 
            ));

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CertificazioniBundle\Entity\CertificazionePagamento'
        ));
    }
}
