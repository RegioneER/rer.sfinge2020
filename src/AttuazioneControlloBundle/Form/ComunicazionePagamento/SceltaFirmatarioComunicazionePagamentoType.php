<?php

namespace AttuazioneControlloBundle\Form\ComunicazionePagamento;

use AnagraficheBundle\Entity\Persona;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class SceltaFirmatarioComunicazionePagamentoType extends \RichiesteBundle\Form\RichiestaType
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
            'choice_label' => function (Persona $persona) {
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
            'data_class' => 'AttuazioneControlloBundle\Entity\Istruttoria\RispostaComunicazionePagamento',
        ));
        $resolver->setRequired("firmatabili");
        $resolver->setRequired("url_indietro");
    }
}
