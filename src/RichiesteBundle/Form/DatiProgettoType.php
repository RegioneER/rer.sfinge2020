<?php

namespace RichiesteBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DatiProgettoType extends RichiestaType {

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('titolo',self::textarea,
            array("label"=>'Titolo (massimo 500 caratteri)', "required"=>true,
            'attr' => array('style' => 'width: 500px', 'rows' => '5'))
            );

        $builder->add('abstract',self::textarea,
            array("label"=>'Abstract: Sintesi del progetto, da pubblicare su web, da cui sia comprensibile in cosa consiste il progetto, gli obiettivi e i risultati attesi(massimo 1300 caratteri)', "required"=>true,
                'attr' => array('style' => 'width: 500px', 'rows' => '10'))
            );

        $builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"], 'disabled' => false));

    }
    
    /*
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'RichiesteBundle\Entity\Richiesta',
            'validation_groups' => array('dati_progetto'),
            'readonly' => false,
        ));
        $resolver->setRequired("url_indietro");
    }
}

