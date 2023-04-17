<?php

namespace IstruttorieBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use IstruttorieBundle\Entity\NucleoIstruttoria;

class NucleoIstruttoriaType extends CommonType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {


        $builder->add('dataNucleo', self::birthday, array(
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
            'required' => true,
            'label' => 'Data del nucleo',
        ));

        //Pulsanti fine form
        $builder->add("pulsanti", self::salva_indietro, array("url" => $options["url_indietro"]));
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'IstruttorieBundle\Entity\NucleoIstruttoria'
        ));
        $resolver->setRequired("url_indietro");
        $resolver->setRequired("lista_tipi");
    }

}
