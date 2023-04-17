<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 29/01/16
 * Time: 13:18
 */

namespace RichiesteBundle\Form;


use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReferenteType extends CommonType
{

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add('tipo_referenza', self::entity, array(
            'class' => 'RichiesteBundle\Entity\TipoReferenza',
            'choices' => $options["tipi_referenza"],
            'choice_label' => "descrizione",
            'required' => true,
            'label' => "Tipologia"
        ));

        $builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"], 'disabled' => false));
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'readonly' => false,
            'data_class' => "RichiesteBundle\Entity\Referente"
        ));
        $resolver->setRequired("url_indietro");
        $resolver->setRequired("tipi_referenza");
    }

}