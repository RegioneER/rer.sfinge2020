<?php

namespace RichiesteBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use RichiesteBundle\Entity\ObiettivoRealizzativo;
use Symfony\Component\Validator\Constraints\NotNull;

class ObiettivoRealizzativoType extends CommonType {
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        /** @var ObiettivoRealizzativo $obiettivo */
        $obiettivo = $builder->getData();
        $totale = $obiettivo->getGgUomoAusiliario() +
                $obiettivo->getGgUomoEsterno() +
                $obiettivo->getGgUomoInterno() +
                $obiettivo->getGgUomoRicerca();

        $codiciAssegnati = $obiettivo->getRichiesta()->getObiettiviRealizzativi()
        ->map(function(ObiettivoRealizzativo $obiettivo){
            return $obiettivo->getCodiceOr();
        })
        ->filter(function(?int $codice) use($obiettivo){
            return $codice != $obiettivo->getCodiceOr();
        })
        ->toArray();
        $codiciPossibili = \array_diff([
                'OR1' => 1,
                'OR2' => 2,
                'OR3' => 3,
                'OR4' => 4,
                'OR5' => 5,
                'OR6' => 6,
            ],$codiciAssegnati
        );

        $builder->add('codice_or', self::choice, [
            'label' => 'Codice',
            'choices_as_values' => true,
            'choices' => $codiciPossibili,
            'placeholder' => '-',
            'required' => true,
        ])
        ->add('titolo_or', self::text, [
            'required' => true,
            'label' => 'Titolo',
        ])
        ->add('mese_avvio_previsto', self::integer, [
            'required' => true,
            'label' => 'Mese avvio',
        ])
        ->add('mese_fine_previsto', self::integer, [
            'required' => true,
            'label' => 'Mese fine',
        ])
        ->add('tipologia', self::choice, [
            'mapped' => false,
            'label' => 'Tipologia',
            'choices_as_values' => true,
            'choices' => [
                'RI' => 'RI',
                'SS' => 'SS',
            ],
            'placeholder' => '-',
            'constraints' => array(new NotNull())
        ])
        ->add('obiettivi_previsti', self::textarea, [
            'required' => true,
            'label' => 'Obiettivi',
        ])
        ->add('attivita_previste', self::textarea, [
            'required' => true,
        ])
        ->add('risultati_attesi', self::textarea, [
            'required' => true,
        ])
        ->add('gg_uomo_interno', self::importo, [
            'required' => true,
            'label' => 'Giorni uomo ricercatori interni',
        ])
        ->add('gg_uomo_ausiliario', self::importo, [
            'required' => true,
            'label' => 'Giorni uomo personale ausiliario interno',
        ])
        ->add('gg_uomo_ricerca', self::importo, [
            'required' => true,
            'label' => 'Giorni uomo personale centri ricerca',
        ])
        ->add('gg_uomo_esterno', self::importo, [
            'required' => true,
            'label' => 'Giorni uomo personale esterno',
        ])
        ->add('gg_uomo_totali', self::importo, [
            'disabled' => true,
            'label' => 'Totale giorni uomo',
            'mapped' => false,
            'data' => $totale,
            'required' => false,
        ])
        ->add('submit', self::salva_indietro, [
            'url' => $options['indietro'],
        ])
        ->setDataMapper(new ObiettivoSpecificoDataMapper());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => ObiettivoRealizzativo::class,
        ])
        ->setRequired('indietro');
    }
}
