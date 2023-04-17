<?php

namespace UtenteBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RicercaUtentiType extends CommonType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        $builder->add('id', self::text, [
            'required' => false,
            'label' => 'Id',
        ]);
        $builder->add('username', self::text, [
            'required' => false,
            'label' => 'Username',
        ]);
        $builder->add('email', self::text, [
            'required' => false,
            'label' => 'Email',
        ]);
        $builder->add('ruoli', self::text, [
            'required' => false,
            'label' => 'Ruolo',
        ]);
        $builder->add('attivo', self::choice, [
            'choice_value' => [$this, "mapping"],
            'required' => false,
            'choices' => ['Si' => true, 'No' => false],
            'choices_as_values' => true,
            'label' => 'Attivo',
        ]);
        $builder->add('id_persona', self::text, [
            'required' => false, 'label' => 'Id persona', ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => 'UtenteBundle\Form\Entity\RicercaUtenti',
        ]);
    }

    public function mapping($currentChoiceKey) {
        if (is_null($currentChoiceKey)) {
            return '';
        }
        return $currentChoiceKey ? '1' : '0';
    }
}
