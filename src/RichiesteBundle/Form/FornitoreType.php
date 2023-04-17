<?php

namespace RichiesteBundle\Form;

use RichiesteBundle\Entity\Fornitore;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FornitoreType extends RichiestaType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('tipologia_fornitore', self::entity, ['class' => 'RichiesteBundle\Entity\TipologiaFornitore',
            'choice_label' => function ($tipologia) {
                return $tipologia->getNome();
            },
            'placeholder' => '-',
            'required' => true,
            'label' => 'Tipologia',
        ]);

        $builder->add('codice_fiscale', self::text, [
            "label" => "Codice Fiscale",
            "required" => true,
        ]);

        $builder->add('denominazione', self::text, [
            "label" => "Ragione sociale",
            "required" => true,
        ]);

        $builder->add('indirizzo', self::indirizzo, [
            'label' => false,
        ]);

        $builder->add('pulsanti', self::salva_indietro, [
            "url" => $options["url_indietro"],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Fornitore::class,
            'url_indietro' => false,
            'validation_groups' => function ($form) {
                $data = $form->getData();
                if (is_object($data->getIndirizzo()->getStato())) {
                    if ('Italia' == $data->getIndirizzo()->getStato()->getDenominazione()) {
                        return ["Default", "statoItalia"];
                    } else {
                        return ["Default"];
                    }
                } else {
                    return ["Default"];
                }
            },
        ]);
    }
}
