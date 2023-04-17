<?php

namespace IstruttorieBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DocumentoNucleoIstruttoriaType extends CommonType {

    public function buildForm(FormBuilderInterface $builder, array $options) {


        $builder->add('documentoFile', self::documento, array(
            "label" => false,
            "lista_tipi" => $options["lista_tipi"],
        ));



        $builder->add("submit", "Symfony\Component\Form\Extension\Core\Type\SubmitType", array("label" => "Carica"));
    }

    public function configureOptions(OptionsResolver $resolver) {

        $resolver->setDefaults(array(
            'data_class' => 'IstruttorieBundle\Entity\DocumentoNucleoIstruttoria',
            'validation_groups' => 'nucleoDocumento',
        ));

        $resolver->setRequired("lista_tipi");
        $resolver->setRequired("url_indietro");
    }

}
