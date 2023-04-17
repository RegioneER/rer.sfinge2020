<?php

namespace RichiesteBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DatiGeneraliType extends RichiestaType
{

	public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$disabled = false;

		if ($options["femminile"]) {
			$builder->add('femminile', self::checkbox, [
				"label" => "Il soggetto richiedente è in possesso dei requisiti di soggetto femminile",
				"required" => false,
            ]);
		}
		if ($options["giovanile"]) {
			$builder->add('giovanile', self::checkbox, [
				"label" => "Il soggetto richiedente è in possesso dei requisiti di soggetto giovanile",
				"required" => false,
            ]);
		}

		if ($options["requisiti_rating"]) {
			$builder->add('requisiti_rating', self::checkbox, [
				"label" => "Il soggetto richiedente è in possesso dei requisiti per l'ottenimento del rating di legalità",
				"required" => false,
            ]);
		}

		if ($options["rating"]) {
			$builder->add('rating', self::checkbox, [
				"label" => "In possesso di rating di legalità",
				"required" => false,
            ]);
		}

        if ($options["incremento_occupazionale"]) {
            $builder->add('incremento_occupazionale', self::checkbox,
                ["label" => "E' previsto un incremento occupazionale?", "required" => false,]);

            if ($options["dati_incremento_occupazionale"]) {
                $builder->add('numero_dipendenti_attuale', self::text,
                    ["label" => "Attuale numero dipendenti a tempo indeterminato", "required" => false,]);

                $builder->add('numero_nuove_unita', self::text,
                    ["label" => "Numero nuove unità", "required" => false,]);
            }
        }

		if ($options["stelle"]) {
			$builder->add('stelle_rating', self::choice, [
				'placeholder' => '-',
				'choices_as_values' => true,
				'choices'  => [
			        '1' => '1',
			        '2' => '2',
			        '3' => '3',
                ],
				'required' => false,
				'label' => 'N° stelle rating'
                ]
			);
		}
		
		if ($options["sede_montana"]) {
			$builder->add('sede_montana', self::checkbox, [
				"label" => "Unità locali situate in area montana",
				"required" => false,
            ]);
		}
		
		// Se non ci sono opzioni torno indietro e basta.
		if (!$options['rating'] && !$options['femminile'] && !$options['giovanile'] && !$options['sede_montana']) {
			$disabled = true;
		}

		$builder->add('pulsanti', self::salva_indietro, ["url" => $options["url_indietro"], 'disabled' => $disabled]);
	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults([
			'data_class' => 'RichiesteBundle\Entity\Richiesta',
			'readonly' => false,
			'rating' => true,
			'requisiti_rating' => true,
			'femminile' => true,
			'giovanile' => true,
			'incremento_occupazionale' => true,
			'dati_incremento_occupazionale' => true,
			'stelle' => true,
			'sede_montana' => false,
			'salva_contributo' => false,
			'validation_groups' => ["dati_generali"]
        ]);

		$resolver->setRequired("url_indietro");
	}

}
