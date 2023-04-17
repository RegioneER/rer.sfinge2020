<?php

namespace AttuazioneControlloBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class ContrattoType extends CommonType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {


        $builder->add('tipologiaSpesa', self::entity, array(
            'class' => 'AttuazioneControlloBundle\Entity\TipologiaSpesa',
            'label' => 'Tipologia spesa',
            'choice_label' => 'descrizione',
            'placeholder' => '-',
            'constraints' => array(new NotNull())
        ));

        $builder->add('tipologiaFornitore', self::entity, array(
            'class' => 'AttuazioneControlloBundle\Entity\TipologiaFornitore',
            'label' => 'Tipologia fornitore',
            'choice_label' => 'descrizione',
            'placeholder' => '-',
            'required' => false,
            'choices' => $options['tipologieFornitore']
        ));

        $builder->add('fornitore', self::text, array(
            'label' => 'Fornitore',
            'constraints' => array(new NotNull())
        ));

        $builder->add('numero', self::text, array(
            'label' => 'Numero contratto',
            'constraints' => array(new NotNull())
        ));

        $builder->add('descrizione', self::textarea, array(
            'label' => 'Descrizione contratto',
            'constraints' => array(new NotNull())
        ));

        $builder->add('importo_contratto_complessivo', self::importo, array(
            "label" => "Importo contratto complessivo",
            'constraints' => array(new NotNull()),
            "currency" => "EUR",
            "grouping" => true,
        ));

        $opt = array(
            'label' => 'Data contratto',
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
        );

        if (isset($options["isBeneficiarioScorrimento"]) && $options["isBeneficiarioScorrimento"]) {
            $opt['required'] = array(new NotNull());
            $opt['constraints'] = array(new NotNull());
        } else {
            $opt['required'] = false;
        }

        $builder->add('dataInizio', self::birthday, $opt);

        if (isset($options["isBeneficiarioScorrimento"]) && $options["isBeneficiarioScorrimento"]) {
            $builder->add('importo_eleggibilita', self::importo, array(
                "label" => "Importo pagato prima del periodo di eleggibilità (importo obbligatorio se la Data inizio è compresa tra 01/05/2016 e 01/01/2017",
                "required" => false,
                "currency" => "EUR",
                "grouping" => true,
            ));
        }


        $builder->add("pulsanti", self::salva_indietro, array("url" => $options["url_indietro"]));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AttuazioneControlloBundle\Entity\Contratto',
            'isBeneficiarioScorrimento' => false
        ));
        $resolver->setRequired("url_indietro");
        $resolver->setRequired("tipologieFornitore");
        $resolver->setRequired("isBeneficiarioScorrimento");
    }

}
