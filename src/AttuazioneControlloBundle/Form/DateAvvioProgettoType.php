<?php

namespace AttuazioneControlloBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateAvvioProgettoType extends CommonType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('data_avvio_effettivo', self::birthday, [
            "label" => "Data avvio progetto effettiva",
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
        ]);

        $builder->add("pulsanti", self::salva_indietro, ["url" => $options["url_indietro"]]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => 'AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta',
            'disabilita_data' => false,
        ]);
        $resolver->setRequired("url_indietro");
    }
}
