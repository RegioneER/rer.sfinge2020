<?php

namespace AttuazioneControlloBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class QuietanzaType extends CommonType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        		
		$builder->add('numero', self::text, array(
            "label" => "Numero",
			"required" => false
            //'constraints' => array(new NotNull())
        ));
        
        $builder->add('data_quietanza', self::birthday, array(
            "label" => "Data",
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',            
            'constraints' => array(new NotNull())
        )); 
		
		$builder->add('tipologia_quietanza', self::entity, array(
            'class'   => "AttuazioneControlloBundle\Entity\TipologiaQuietanza",
            "label" => "Tipologia",
            "choices" => $options["tipologie_quietanza"],
            'placeholder' => '-',
            'constraints' => array(new NotNull())
        ));
        
      
        if ($options["documento_caricato"] == false) {
			$builder->add('documento_quietanza', self::documento_simple, array(
				"label" => false,
				'constraints' => array(new NotNull()),
				"opzionale" => false
			));
		}        

//        
//        $builder->add('importo', self::importo, array(
//            "label" => "Importo",
//            'constraints' => array(new NotNull()),
//			"currency" => "EUR",
//			"grouping" => true
//        ));
//        $builder->add('data_valuta', self::birthday, array(
//            "label" => "Data valuta",
//			'widget' => 'single_text',
//			'input' => 'datetime',
//			'format' => 'dd/MM/yyyy',            
//            'constraints' => array(new NotNull())
//        ));  

		$builder->add("pulsanti", self::salva_indietro, array("url" => $options["url_indietro"]));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AttuazioneControlloBundle\Entity\QuietanzaGiustificativo'
        ));
        
        $resolver->setRequired("url_indietro");
        $resolver->setRequired("tipologie_quietanza");
		$resolver->setRequired("documento_caricato");
    }
}
