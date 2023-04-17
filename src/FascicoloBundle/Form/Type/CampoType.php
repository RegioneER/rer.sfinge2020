<?php

namespace FascicoloBundle\Form\Type;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\CallbackTransformer;

class CampoType extends CommonType {
    protected $evidenziato;

    public function __construct($evidenziato) {
        $this->evidenziato = $evidenziato;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('label', self::textarea, [
            'required' => true,
            'label' => 'Label',
            'attr' => [
                'rows' => 4,
            ],
        ]);
        $builder->add('tipoCampo', self::entity, [
            'class' => 'FascicoloBundle\Entity\TipoCampo',
            'property' => 'nome',
            'label' => 'Tipo',
            'placeholder' => '-',
        ]);
        $builder->add('alias', self::text, [
            'required' => true,
            'label' => 'Alias',
        ]);
        $builder->add('required', self::choice, [
            'choice_value' => [$this, "mapping"],
            'label' => 'Obbligatorio',
            'choices' => ['
			Si' => true,
                'No' => false,
            ],
            'choices_as_values' => true,
            'expanded' => true,
            'placeholder' => false,
        ]);
        $builder->add('callbackPresenza', self::text, [
            'required' => false,
            'label' => 'Callback presenza',
        ]);

        if ($this->evidenziato) {
            $builder->add('evidenziato', self::choice, [
                'choice_value' => [$this, "mapping"],
                'required' => true,
                'choices' => ['Si' => true, 'No' => false],
                'choices_as_values' => true,
                'expanded' => true,
                'placeholder' => false,
            ]);
        }
        $builder->add('expanded', self::choice, [
            'choice_value' => [$this, "mapping"],
            'label' => 'Tipo controlli *',
            'choices' => ['Menu a tendina' => false,
                'Radio/Checkbox' => true, ],
            'choices_as_values' => true,
            'expanded' => true, 'required' => false,
            'placeholder' => false,
        ]);
        $builder->add('multiple', self::choice, [
            'choice_value' => [$this, "mapping"],
            'label' => 'Selezione *',
            'choices' => ['Singola' => false, 'Multipla' => true, ],
            'choices_as_values' => true,
            'expanded' => true,
            'required' => false,
            'placeholder' => false,
        ]);
        $builder->add('scelte', self::textarea, [
            'required' => false,
            'label' => 'Scelte ammesse',
        ]);
        $builder->add('query', self::textarea, [
            'required' => false,
            'label' => 'Query scelte ammesse',
        ]);
        $builder->add('precisione', self::integer, [
            'required' => false,
            'label' => 'Numero cifre decimali',
        ]);
        $builder->add('note', self::textarea, [
            'required' => false,
            'label' => 'Note',
        ]);
        $builder->add('righeTextArea', self::integer, [
            'required' => false,
            'label' => 'Righe area di testo',
        ]);

        $builder->add('submit', self::submit, [
            'label' => 'Salva',
        ]);

        $builder->get('scelte')
            ->addModelTransformer(new CallbackTransformer(
                // Trasforma i <br/> in \n
                function ($originalDescription) {
                    return preg_replace('#<br\s*/?>#i', "\n", $originalDescription);
                },
                function ($submittedDescription) {
                    // rimuove gli HTML tags (eccetto br,p)
                    $cleaned = strip_tags($submittedDescription, '<br><br/><p>');

                    // trasforma ogni \n in <br/>
                    return str_replace("\n", '<br/>', $cleaned);
                }
            ));
    }

    public function mapping($currentChoiceKey) {
        if (is_null($currentChoiceKey)) {
            return '';
        }
        return $currentChoiceKey ? '1' : '0';
    }

    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class' => 'FascicoloBundle\Entity\Campo',
        ]);
    }
}
