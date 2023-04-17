<?php

namespace CertificazioniBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Regex;

class AssociazioneCertificazionePagamentiType extends CommonType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$builder->add('pagamenti_estesi', self::collection, array(
			'entry_type' => "CertificazioniBundle\Form\CertificazionePagamentoType",
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
            'data_class' => 'CertificazioniBundle\Entity\Certificazione'
        ));
        
        $resolver->setRequired("url_indietro");
    }
}
