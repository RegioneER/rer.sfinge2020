<?php

namespace AttuazioneControlloBundle\Form\ControlliStabilita;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class VerbaleSopralluogoControlloType extends CommonType {

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add('data_controllo', self::birthday, array(
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
            'required' => false,
            'disabled' => false,
            'label' => 'Data controllo',
        ));

        $builder->add('tipo_sede_fase_spr', self::choice, [
            'choices' => [
                'Sede legale' => 'LEGALE',
                'Unità locale' => 'LOCALE',
            ],
            "label" => "Sede del sopralluogo: ",
            'choices_as_values' => true,
            'constraints' => [new NotNull()],
        ]);

        $builder->add('indirizzo', self::indirizzo, [
            'readonly' => false,
            "validation_groups" => ['Default']
        ]);

        $builder->add('acquisita_fase_spr', self::AdvancedTextType, array(
            'disabled' => false,
            'label' => 'Acquisita/visionata durante il sopralluogo',
            'constraints' => array(new NotNull())));

        $builder->add('richiesta_fase_spr', self::AdvancedTextType, array(
            'disabled' => false,
            'label' => 'RIchiesta perchè non disponibile durante il sopralluogo',
            'constraints' => array(new NotNull())));

        $builder->add('conclusioni_fase_spr', self::AdvancedTextType, array(
            'disabled' => false,
            'label' => 'Conclusioni del sopralluogo',
            'constraints' => array(new NotNull())));

        $builder->add('osservazioni_ben_fase_spr', self::AdvancedTextType, array(
            'disabled' => false,
            'label' => 'Eventuali osservazioni del beneficiario',
            'constraints' => array(new NotNull())));

        $builder->add('spese_ammesse', self::importo, array(
            "label" => "Le spese ammesse sono pari a €",
            'constraints' => array(new NotNull()),
            "currency" => "EUR",
            "grouping" => true
        ));

        $builder->add('spese_rivalutazione', self::importo, array(
            "label" => "Le spese sulle quali si richiede una rivalutazione sono pari a €",
            'constraints' => array(new NotNull()),
            "currency" => "EUR",
            "grouping" => true
        ));

        $builder->add('spese_non_ammissibili', self::importo, array(
            "label" => "Le spese non ammissibili sono pari a €",
            'constraints' => array(new NotNull()),
            "currency" => "EUR",
            "grouping" => true
        ));

        $builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"]));
    }

    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);

        $resolver->setDefaults(array(
            'data_class' => 'AttuazioneControlloBundle\Entity\Controlli\ControlloProgetto',
            "dataIndirizzo" => null
        ));

        $resolver->setRequired("url_indietro");
    }

}
