<?php

namespace AttuazioneControlloBundle\Form\Revoche;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class RevocaAsse1Type extends RevocaType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $read_only = $options["readonly"];
        $disabled = $options["readonly"];

        if (true == $read_only) {
            $attr = ['readonly' => 'readonly'];
        } else {
            $attr = [];
        }

        parent::buildForm($builder, $options);

        $builder->add('con_penalita', self::choice, [
            "label" => "Revoca che ha comportato penalità",
            "required" => true,
            "expanded" => true,
            "multiple" => false,
            'choices_as_values' => true,
            'choices' => [
                'Si' => true,
                'No' => false,
            ],
            'disabled' => $disabled,
        ]);

        $builder->add('importo_penalita', self::importo, [
            "label" => "Importo della penalità/multa",
            'disabled' => $disabled,
            "currency" => "EUR",
            "grouping" => true,
            'required' => false,
        ]);

        $builder->add('data_corresponsione', self::birthday, [
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
            'disabled' => $disabled,
            'label' => 'Data corresponsione',
            'attr' => $attr,
            'required' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => 'AttuazioneControlloBundle\Entity\Revoche\Revoca',
            'readonly' => false,
            "mostra_indietro" => true,
        ]);

        $resolver->setRequired("readonly");
        $resolver->setRequired("url_indietro");
    }
}
