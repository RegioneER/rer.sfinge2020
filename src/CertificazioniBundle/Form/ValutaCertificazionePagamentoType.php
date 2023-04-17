<?php

namespace CertificazioniBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ValutaCertificazionePagamentoType extends CommonType {
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('importo_taglio', self::text, array(
            "label" => "Importo Taglio",
            "required" => false,
        ));

        $builder->add('tipologia_taglio', self::choice, array(
            'choices_as_values' => true,
            'choices' => array('AdC' => 'AdC'/*, 'AdA' => 'AdA'*/),
            "required" => false,
        ));

        $builder->add('nota_taglio',self::textarea,
            array(
            "label" => 'Nota (min: 2, max: 1300 caratteri)',
            'attr' => array('style' => 'width: 500px', 'rows' => '10'),
            "required" => false,
           ));

        $builder->add("pulsanti", self::salva_indietro, array("url" => $options["url_indietro"]));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'CertificazioniBundle\Entity\CertificazionePagamento',
        ));

        $resolver->setRequired("url_indietro");
    }
}
