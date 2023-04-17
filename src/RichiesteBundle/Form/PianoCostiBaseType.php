<?php

namespace RichiesteBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class PianoCostiBaseType extends RichiestaType {

	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder->add('voci_piano_costo', self::collection, array(
			'entry_type' => "RichiesteBundle\Form\CampoPianoCostoType",
			'allow_add' => false,
			"label" => "Compilazione piano costi",
			'entry_options' => array(
				'annualita' => $options['annualita'],
				'labels_anno' => $options['labels_anno'],
				'totale' => $options['totale'],
				'descrizione' => $options['descrizione'],
				'disable_piano' => $options['disable_piano']
			)
		));

		if ($options['modalita_finanziamento_attiva']) {
			$builder->add('voci_modalita_finanziamento', self::collection, array(
				'entry_type' => "RichiesteBundle\Form\CampoModalitaFinanziamentoType",
				'entry_options' => array("mostra_importo_modalita_finanziamento" => $options["mostra_importo_modalita_finanziamento"],
					"decimali_percentuale" => $options["decimali_percentuale_modalita_finanziamento"]),
				'allow_add' => false,
				"label" => "Compilazione modalità finanziamento",
			));
		}

		if ($options['abilita_contr_impe'] == true) {
			$builder->add('contributo', self::importo, array("required" => false,
				"label" => 'Contributo',
				"currency" => "EUR",
				"grouping" => true,
				'disabled' => $options['disabled'] || $options['disable_piano']));
			
			$builder->add('impegno', self::importo, array("required" => false,
				"label" => 'Impegno',
				"currency" => "EUR",
				"grouping" => true,
				'disabled' => $options['disabled'] || $options['disable_piano']));
		}

		$builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"], 'disabled' => false));
	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'RichiesteBundle\Entity\Proponente',
			'readonly' => false,
			'constraints' => array(new Valid()),
			'mostra_importo_modalita_finanziamento' => true,
			'decimali_percentuale_modalita_finanziamento' => 8,
			'totale' => false,
			'descrizione' => false,
			'disable_piano' => false,
			'abilita_contr_impe' => false
		));

		$resolver->setRequired("url_indietro");
		$resolver->setRequired("annualita");
		$resolver->setRequired("labels_anno");
		$resolver->setRequired("modalita_finanziamento_attiva");
	}

}
