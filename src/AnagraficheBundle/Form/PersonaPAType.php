<?php

namespace AnagraficheBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonaPAType extends CommonType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $data = $builder->getData();

        $read_only = $options["readonly"];
        $disabled = $options["readonly"] || $options["disabled"];
        $disabilitaEmail = $options["disabilitaEmail"] || $disabled;
        $dataIndirizzo = $options["dataIndirizzo"];

        $attr = [];
        if (true == $read_only) {
            $attr = ['readonly' => 'readonly'];
        }

        $attr_email = [];
        if (true == $disabilitaEmail) {
            $attr_email = ['readonly' => 'readonly'];
        }

        $builder->add('nome', self::text, [
            'required' => true,
            'disabled' => $disabled,
            'label' => 'Nome',
            'attr' => $attr,
        ]);
        $builder->add('cognome', self::text, [
            'required' => true,
            'disabled' => $disabled,
            'label' => 'Cognome',
            'attr' => $attr,
        ]);
        $builder->add('telefono_principale', self::text, [
            'required' => true,
            'disabled' => $disabled,
            'label' => 'Telefono',
            'attr' => $attr,
        ]);
        $builder->add('email_principale', self::text, [
            'required' => true,
            'disabled' => $disabilitaEmail,
            'label' => 'Email',
            'attr' => $attr_email,
        ]);

        $builder->add('salva_invia', self::salva_indietro, [
            'mostra_indietro' => $options['mostra_indietro'],
            'url' => $options["url_indietro"],
            'disabled' => $disabled,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => 'AnagraficheBundle\Form\Entity\PersonaPA',
            'readonly' => false,
            'disabilitaEmail' => true,
            "mostra_indietro" => true,
            'disabilitaCf' => true,
        ]);

        $resolver->setRequired("dataIndirizzo");
        $resolver->setRequired("dataPersona");
        $resolver->setRequired("url_indietro");
    }
}
