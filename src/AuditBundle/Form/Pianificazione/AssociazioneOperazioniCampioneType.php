<?php

namespace AuditBundle\Form\Pianificazione;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssociazioneOperazioniCampioneType extends CommonType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$builder->add('campioni_estesi', self::collection, array(
			'entry_type' => "AuditBundle\Form\Pianificazione\AuditCampioneOperazioneType",
			'allow_add' => false,
			"label" => false
		)); 
        
        $builder->add("pulsanti", self::salva_indietro, array("url" => $options["url_indietro"]));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AuditBundle\Entity\AuditOperazione'
        ));
        
        $resolver->setRequired("url_indietro");
    }
}
