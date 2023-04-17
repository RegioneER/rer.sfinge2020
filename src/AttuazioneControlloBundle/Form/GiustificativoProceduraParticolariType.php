<?php

namespace AttuazioneControlloBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\Constraints\Length;

class GiustificativoProceduraParticolariType extends CommonType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('denominazione_fornitore', self::text, array(
            "label" => "Denominazione",
            'constraints' => array(new NotNull())
        ));
            
        $builder->add('codice_fiscale_fornitore', self::text, array(
            "label" => "Codice fiscale",
			'required' => false,
        ));
        
        $builder->add('descrizione_giustificativo', self::textarea, array(
            "label" => "Descrizione",
			'required' => false,
        ));  
        
        $builder->add('numero_giustificativo', self::text, array(
            "label" => "Numero giustificativo",
			'required' => false,
        ));
        
        $builder->add('data_giustificativo', self::birthday, array(
            "label" => "Data",
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',            
            'constraints' => array(new NotNull())
        ));
        $builder->add('importo_imponibile_giustificativo', self::importo, array(
            "label" => "Importo imponibile (€)",
            'constraints' => array(new NotNull()),
            "currency" => "EUR",
            "grouping" => true
        ));
        $builder->add('importo_iva_giustificativo', self::importo, array(
            "label" => "Iva (€)",
            'constraints' => array(new NotNull()),
            "currency" => "EUR",
            "grouping" => true
        ));
        $builder->add('importo_giustificativo', self::importo, array(
            "label" => "Importo totale (€)",
            'constraints' => array(new NotNull()),
			"currency" => "EUR",
			"grouping" => true
        ));
		if($options["documento_caricato"] == false) {
			$builder->add('documento_giustificativo', self::documento_simple, array(
				"label" => false,
				'required' => false,
				"opzionale" => true
			));        
		}
//        $builder->add('importo_richiesto', self::importo, array(
//            "label" => "Importo richiesto (€)",
//            'constraints' => array(new NotNull()),
//			"currency" => "EUR",
//			"grouping" => true
//        ));  
        
        $builder->add('nota_beneficiario', self::textarea, array(
            "label" => "Nota",
            'required' => false
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
		$resolver->setRequired("documento_caricato");
    }
}
