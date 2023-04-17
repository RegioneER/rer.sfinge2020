<?php

namespace AuditBundle\Form\Pianificazione;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class AssociazionePianificazioneRequisitoType extends CommonType
{
   
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$builder->add('audit_requisiti_estesi', self::collection, array(
			'entry_type' => "AuditBundle\Form\Pianificazione\PianificazioneRequisitoType",
			'allow_add' => false,
			"label" => false
		)); 
        
        $builder->add("pulsanti", self::salva_indietro, array("url" => $options["url_indietro"]));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AuditBundle\Entity\AuditOrganismo'
        ));
        
        $resolver->setRequired("url_indietro");
    }
}
