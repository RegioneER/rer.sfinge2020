<?php

namespace RichiesteBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DichirazioniDnshType extends CommonType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('non_arreca', self::checkbox, array(
            'required' => false,
            'label' => $options['non_arreca']
        ));

        $builder->add('adotta_misure', self::checkbox, array(
            'label' => $options['adotta_misure'],
            'required' => false,
        ));

        $builder->add('descrizione_adotta_misure', self::textarea, array(
            'label' => false,
            'required' => false,
            'attr' => array('style' => 'width:600px')
        ));

        $builder->add('specifica_documentazione', self::checkbox, array(
            'label' => $options['specifica_documentazione'],
            'required' => false,
        ));

        $builder->add('descrizione_specifica_documentazione', self::textarea, array(
            'label' => false,
            'required' => false,
            'attr' => array('style' => 'width:600px')
        ));

        $builder->add('submit', self::salva_indietro, array('label' => 'Salva', 'url' => $options['url_indietro'],));
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'RichiesteBundle\Entity\DichiarazioneDsnh',
        ));

        $resolver->setRequired("url_indietro");
        $resolver->setRequired("non_arreca");
        $resolver->setRequired("adotta_misure");
        $resolver->setRequired("specifica_documentazione");
    }

}
