<?php

namespace AttuazioneControlloBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AttuazioneControlloBundle\Form\Entity\TipoVariazione;
use AnagraficheBundle\Entity\Persona;
use AttuazioneControlloBundle\Entity\VariazioneRichiesta;

class TipoVariazioneType extends CommonType {
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $variazioneRichiesta = new VariazioneRichiesta($options['data']->getAtc());
        $tipiVariazioni = $variazioneRichiesta->getTipiVariazioni();
        
        $builder->add('tipoVariazione', self::choice, [
            'label' => 'Tipo di variazione',
            'choices_as_values' => true,
            'choices' => $tipiVariazioni,
            'placeholder' => '-',
            'required' => true,
        ]);

        $builder->add('firmatario', self::entity, [
            'class' => Persona::class,
            "label" => "Firmatario",
            'choice_label' => function (Persona $persona) {
                return $persona->getNome() . " " . $persona->getCognome() . " ( " . $persona->getCodiceFiscale() . " )";
            },

            "choices" => $options["firmatabili"],
            'placeholder' => '-',
            'required' => true,
        ]);

        $builder->add("submit", self::salva_indietro, [
            "url" => $options["url_indietro"],
        ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => TipoVariazione::class,
        ]);
        $resolver->setRequired([
            "firmatabili",
            "url_indietro",
        ]);
    }
}
