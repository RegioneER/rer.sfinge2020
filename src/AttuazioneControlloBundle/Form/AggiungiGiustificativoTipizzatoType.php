<?php

namespace AttuazioneControlloBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class AggiungiGiustificativoTipizzatoType extends CommonType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('tipologia_giustificativo', self::entity, array(
            'class'   => "AttuazioneControlloBundle\Entity\TipologiaGiustificativo",
            "label" => "Tipologia di giustificativo",   
            "choices" => $options["tipologie"],
			'choice_label' => 'getDescrizioneTabGiustificativi',
            'expanded' => true,
            'constraints' => array(new NotNull())
        ));              

        $builder->add("pulsanti", self::salva_indietro, array("url" => $options["url_indietro"]));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AttuazioneControlloBundle\Entity\GiustificativoPagamento'
        ));
        
        $resolver->setRequired("url_indietro");
        $resolver->setRequired("tipologie");
    }
}
