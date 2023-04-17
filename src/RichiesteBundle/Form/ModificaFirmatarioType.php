<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 25/01/16
 * Time: 12:01
 */

namespace RichiesteBundle\Form;


use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;


class ModificaFirmatarioType extends RichiestaType
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
            'data_class' => 'RichiesteBundle\Entity\Richiesta',
        ));
        $resolver->setRequired("firmatabili");
        $resolver->setRequired("url_indietro");
    }
}
