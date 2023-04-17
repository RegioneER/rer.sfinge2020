<?php

namespace AttuazioneControlloBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class PagamentoVocePianoCostoType extends CommonType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add('voce_piano_costo', self::entity, array(
            'class' => "RichiesteBundle\Entity\VocePianoCosto",
            "choice_label" => "mostraLabelRendicontazione",
            "label" => "Voce piano costo",
            "choices" => $options["voci_piano_costo"],
            'placeholder' => '-',
            'constraints' => array(new NotNull())
        ));

        if (count($options["annualita"]) > 1) {
            $builder->add('annualita', self::choice, array(
                "label" => $options["label_annualita"],
                'choices_as_values' => true,
                "choices" => \array_flip($options["annualita"]),
                'placeholder' => '-',
                'constraints' => array(new NotNull())
            ));
        }

        $builder->add('importo', self::importo, array(
            "label" => "Importo rendicontato per voce di costo",
            'constraints' => array(new NotNull()),
            "currency" => "EUR",
            "grouping" => true
        ));

        $builder->add("pulsanti", self::salva_indietro, array("url" => $options["url_indietro"]));
    }

    /**
     * @param OptionsResolver $resolver
     * 
     * l'option label_annualita è stata inserita perchè per il bando 24 è stato usato impropriamente il concetto di annualità per 
     * mappare le azioni del pioano costi (high seniority) per cui per il bando 24 dobbiamo customizzare la label di conseguenza..
     * trattasi di piano costi singola annualita multi-azione (teccà mancia)
     * 
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AttuazioneControlloBundle\Entity\VocePianoCostoGiustificativo',
            'label_annualita' => 'Annualità'
        ));

        $resolver->setRequired("url_indietro");
        $resolver->setRequired("voci_piano_costo");
        $resolver->setRequired("annualita");
    }

}
