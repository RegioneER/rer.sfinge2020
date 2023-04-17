<?php

namespace IstruttorieBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class IstruttoriaPianoCostiBaseType extends \BaseBundle\Form\CommonType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('voci_piano_costo', self::collection, array(
            'entry_type' => "IstruttorieBundle\Form\VocePianoCostoType",
            'allow_add' => false,
            "label" => "Compilazione piano costi",
            'entry_options' => array(
                'annualita' => $options['annualita'],
            ),
        ));

        $builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"], 'disabled' => false));
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'RichiesteBundle\Entity\Proponente',
            'readonly' => false,
            'constraints' => array(new Valid()),
        ));

        $resolver->setRequired("url_indietro");
        $resolver->setRequired("annualita");
        $resolver->setRequired("modalita_finanziamento_attiva");
    }
}
