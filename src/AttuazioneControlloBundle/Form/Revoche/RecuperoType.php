<?php

namespace AttuazioneControlloBundle\Form\Revoche;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class RecuperoType extends CommonType {

    public function __construct() {
        
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $read_only = $options["readonly"];
        $disabled = $options["readonly"];

        if ($read_only == true) {
            $attr = array('readonly' => 'readonly');
        } else {
            $attr = array();
        }

        $builder->add('tipo_fase_recupero', self::entity, array(
            'class' => 'AttuazioneControlloBundle\Entity\Revoche\TipoFaseRecupero',
            'placeholder' => '-',
            'required' => true,
            'label' => 'Fase recupero',
            'disabled' => $disabled,
            'attr' => $attr,
            'constraints' => array(new NotNull())
        ));


        $builder->add('tipo_specifica_recupero', self::entity, array(
            'class' => 'AttuazioneControlloBundle\Entity\Revoche\TipoSpecificaRecupero',
            'placeholder' => '-',
            'required' => true,
            'label' => 'Specifica del recupero',
            'disabled' => $disabled,
            'attr' => $attr,
        ));

        $builder->add('contributo_corso_recupero', self::importo, array(
            "label" => "Contributo totale da recuperare",
            'disabled' => $disabled,
            "currency" => "EUR",
            "grouping" => true,
            'required' => true,
            'attr' => $attr,
        ));

        $builder->add('importo_interesse_legale', self::importo, array(
            "label" => "Interessi legali",
            'disabled' => true,
            "currency" => "EUR",
            "grouping" => true,
            'required' => false,
        ));

        $builder->add('importo_interesse_mora', self::importo, array(
            "label" => "Interessi di mora",
            'disabled' => true,
            "currency" => "EUR",
            "grouping" => true,
            'required' => false,
        ));

        $builder->add('contributo_restituito', self::importo, array(
            "label" => "Contributo restituito",
            'disabled' => true,
            "currency" => "EUR",
            "grouping" => true,
            'required' => false,
        ));

        $builder->add('contributo_non_recuperato', self::importo, array(
            "label" => "Contributo non recuperato",
            'disabled' => $disabled,
            "currency" => "EUR",
            "grouping" => true,
            'required' => true,
        ));

        $builder->add('azioni_mancato_recupero', self::textarea, array(
            'required' => true,
            'disabled' => $disabled,
            'label' => 'Azioni in caso di mancato recupero',
            'attr' => $attr
        ));

        if ($options["penalita"] == true) {
            $builder->add('importo_sanzione', self::importo, array(
                "label" => "Importo sanzione",
                'disabled' => $disabled,
                "currency" => "EUR",
                "grouping" => true,
                'required' => true,
            ));
        }

        $builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"], 'disabled' => $disabled, 'mostra_indietro' => $options["mostra_indietro"]));
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AttuazioneControlloBundle\Entity\Revoche\Recupero',
            'readonly' => false,
            'penalita' => false,
            "mostra_indietro" => true
        ));

        $resolver->setRequired("readonly");
        $resolver->setRequired("url_indietro");
    }

}
