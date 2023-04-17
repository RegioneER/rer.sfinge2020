<?php

namespace IstruttorieBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class SceltaFirmatarioType extends \RichiesteBundle\Form\RichiestaType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('firmatario', self::entity, array(
            'class'   => "AnagraficheBundle\Entity\Persona",
            "label" => "Firmatario",
            'choice_label' => function ($persona) {
                return $persona->getNome()." ".$persona->getCognome()." ( ".$persona->getCodiceFiscale()." )";
            },
            "choices"=>$options["firmatabili"],
            'constraints' => array(new NotNull())
        ));


        $builder->add("pulsanti",self::salva_indietro,array("url"=>$options["url_indietro"]));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'IstruttorieBundle\Entity\RispostaIntegrazioneIstruttoria',
        ));
        $resolver->setRequired("firmatabili");
        $resolver->setRequired("url_indietro");
    }
}
