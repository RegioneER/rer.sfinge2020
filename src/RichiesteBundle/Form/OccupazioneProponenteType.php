<?php

namespace RichiesteBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OccupazioneProponenteType extends RichiestaType {
    protected $tipologiaA = "Si impegna ad aumentare l'occupazione complessiva dell'impresa, prevedendo alla fine del progetto un incremento di occupati a tempo indeterminato di un numero pari a almeno 2 unità, e richiede pertanto la maggiorazione di 10 punti percentuali del contributo.";
    protected $tipologiaB = "Si impegna ad aumentare l'occupazione complessiva dell'impresa, prevedendo alla fine del progetto un incremento di occupati a tempo indeterminato di un numero pari a almeno 3 unità, e richiede pertanto la maggiorazione di 10 punti percentuali del contributo.";
    protected $tipologiaDefault = "Si impegna ad assumere a tempo indeterminato entro il 31/12/2017  nuovo personale e richiede la maggiorazione di 5 punti percentuali del contributo.";

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $labelRichiestaMaggiorazioneDefault = $this->tipologiaDefault;
        $numeroDipendentiAttuali = $options['numero_dipendenti_attuale'];
        $numeroDipendentiFuturi = $options['numero_dipendenti_da_assumere'];

        if ('TIPOLOGIA_A' == $options['tipologia']) {
            $labelRichiestaMaggiorazioneDefault = $this->tipologiaA;
            $numeroDipendentiAttuali = $numeroDipendentiAttuali ?? true;
            $numeroDipendentiFuturi = $numeroDipendentiFuturi ?? false;
        }
        if ('TIPOLOGIA_B' == $options['tipologia']) {
            $labelRichiestaMaggiorazioneDefault = $this->tipologiaB;
            $numeroDipendentiAttuali = $numeroDipendentiAttuali ?? false;
            $numeroDipendentiFuturi = $numeroDipendentiFuturi ?? true;
        }

        $labelRichiestaMaggiorazione = $options['label_richiesta_maggiorazione'] ?? $labelRichiestaMaggiorazioneDefault;

        $builder->add('richiesta_maggiorazione_contributo', self::checkbox, [
            'label' => $labelRichiestaMaggiorazione,
            "attr" => ["onChange" => 'calcolaTotaleSezioneEContributo()'],
        ]);

        if ($numeroDipendentiAttuali) {
            $builder->add('numero_dipendenti_attuale', self::numero, [
                'label' => 'Numero di dipendenti attuale',
            ]);
        }

        if ($numeroDipendentiFuturi) {
            $builder->add('numero_dipendenti_da_assumere', self::numero, [
                'label' => 'Numero di dipendenti da assumere',
            ]);
        }

        if ($options['spin_off_universitario']) {
			$builder->add('spin_off_universitario', self::checkbox, [
				'label' => $options['spin_off_universitario'],
				"attr" => ["onChange" => 'calcolaTotaleSezioneEContributo()'],
			]);
        }
        if ($options['sviluppo_rete_sistema']) {
			$builder->add('sviluppo_rete_sistema', self::checkbox, [
				'label' => $options['sviluppo_rete_sistema'],
				"attr" => ["onChange" => 'calcolaTotaleSezioneEContributo()'],
			]);
        }
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => \RichiesteBundle\Entity\OccupazioneProponente::class,
            'readonly' => false,
            'required' => false,
            'label_richiesta_maggiorazione' => null,
            'numero_dipendenti_attuale' => null,
            'numero_dipendenti_da_assumere' => null,
            'tipologia' => null,
            'spin_off_universitario' => null,
            'sviluppo_rete_sistema' => null,
        ]);
    }
}
