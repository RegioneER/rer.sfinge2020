<?php


namespace RichiesteBundle\Form;


use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;


class RichiestaGenericoType extends CommonType
{

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

        $builder->add("submit",self::submit,array("label"=>"Salva"));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'RichiesteBundle\Entity\Richiesta'
        ));
        $resolver->setRequired("firmatabili");
    }
}
