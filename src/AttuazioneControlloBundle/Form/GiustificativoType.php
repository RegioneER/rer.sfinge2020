<?php

namespace AttuazioneControlloBundle\Form;

use AttuazioneControlloBundle\Entity\GiustificativoPagamento;
use AttuazioneControlloBundle\Entity\TipologiaGiustificativo;
use BaseBundle\Form\CommonType;
use DocumentoBundle\Entity\DocumentoFile;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\Constraints\Length;

class GiustificativoType extends CommonType
{
	
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var GiustificativoPagamento $giustificativo */
        $giustificativo = $options['data'];

        $readOnly = false;

        if($giustificativo->isFatturaElettronica()) {
            $readOnly = true;
        }
        
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();

            $giustificativo = $event->getData();

            if($giustificativo->getDocumentoGiustificativo() instanceof DocumentoFile) {
                $file = $giustificativo->getDocumentoGiustificativo();
            } else {
                $file = $form->get("documento_giustificativo")->getData();
            }

            /*if(!$giustificativo->getTipologiaGiustificativo()->isTipologiaFatturaElettronica() && $file->isFileXml()) {
                $form->get("documento_giustificativo")->get("file")->addError(new FormError("Formato del file non corretto per la tipologia di documento selezionata."));
            }*/
        });

        if($options['spese_personale'] == true) {
            $labelForn = "Denominazione/Nominativo";
        }else {
            $labelForn =  "Denominazione";
        }
        $builder->add('denominazione_fornitore', self::text, array(
            "label" => $labelForn,
            'constraints' => array(new NotNull()),
            'read_only' => $readOnly,
        ));
            
		// vincolo rilassato per le spese di personale
        $builder->add('codice_fiscale_fornitore', self::text, array(
            "label" => "Codice fiscale",
            'constraints' => array(new Length(array("min" => 2, "max" => 16))),
            'read_only' => $readOnly,
        ));
		
		if (!is_null($options['tipologieGiustificativo'])) {
			$builder->add('tipologia_giustificativo', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', array(
				'class' => 'AttuazioneControlloBundle:TipologiaGiustificativo',
				'choices' => $options["tipologieGiustificativo"],
				'choice_label' => 'descrizione',
				'required' => true,
				'disabled' => false,
				'placeholder' => '-',
				'constraints' => array(new NotNull()),
			));
		}
		
        $builder->add('numero_giustificativo', self::text, array(
            "label" => "Numero",
            'constraints' => array(new NotNull()),
            'read_only' => $readOnly,
        ));
        
        $builder->add('data_giustificativo', self::birthday, array(
            "label" => "Data",
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',            
            'constraints' => array(new NotNull()),
            'read_only' => $readOnly,
        ));		

        $builder->add('importo_giustificativo', self::importo, array(
            "label" => "Importo giustificativo (€)",
            'constraints' => array(new NotNull()),
			"currency" => "EUR",
			"grouping" => true,
            'read_only' => $readOnly,
        ));
		
		
		// vincolo rilassato per le spese di personale
		if($options["documento_caricato"] == false) {
			$builder->add('documento_giustificativo', self::documento_simple, array(
				"label" => false,
				'constraints' => array(new Valid()),
				"opzionale" => true,
			));        
		}		
		
		if (!is_null($options['proponenti'])) {
			$builder->add('proponente', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', array(
				'class' => 'RichiesteBundle:Proponente',
				'choices' => $options["proponenti"],
				'choice_label' => 'denominazione',
				'required' => true,
				'disabled' => false,
				'placeholder' => '-',
				'constraints' => array(new NotNull()),

			));
		}
        
        $builder->add('nota_beneficiario', self::textarea, array(
            "label" => "Nota/Descrizione",
            'required' => false,
            'read_only' => $readOnly,
        ));

        if (!is_null($options['tipologieGiustificativo'])) {
            /** @var TipologiaGiustificativo $tipologiaGiustificativo */
            foreach ($options['tipologieGiustificativo'] as $tipologiaGiustificativo) {
                if($tipologiaGiustificativo->isTipologiaFatturaElettronica()) {
                    $builder->add('id_tipologia_fattura_elettronica', self::hidden, [
                        'data' => $tipologiaGiustificativo->getId(),
                        'mapped' => false,
                    ]);
                }
            }
        }

        $builder->add("pulsanti", self::salva_indietro, array("url" => $options["url_indietro"]));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {        
		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Entity\GiustificativoPagamento',
			'proponenti' => null,
			'spese_personale' => false,
		));
		
        $resolver->setRequired("url_indietro");
		$resolver->setRequired("documento_caricato");
		$resolver->setRequired("tipologieGiustificativo");
    }
}
