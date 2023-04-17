<?php

namespace AuditBundle\Form\Pianificazione;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CampioneOperazioneType extends CommonType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder->add('nome', self::text, array(
			'required' => true,
			'disabled' => $options['disabled'],
			'label' => 'Nome')
		);

		$builder->add('campione_stratificato', self::choice, array(
			'required' => true,
			'disabled' => $options['disabled'],
			'choices' => array(
				'Si' => true,
				'No' => false,
			),
			'choices_as_values' => true,
			'label' => 'Adozione di un campione stratificato'
				)
		);

		$builder->add('modalita_campionamento', self::entity, array(
			'class' => 'AuditBundle\Entity\MetodologiaCampionamento',
			'expanded' => false,
			'multiple' => false,
			'required' => true,
			'disabled' => $options['disabled'],
			'label' => 'Modalità di campionamento usata'
		));

		$builder->add('tipo_campione', self::entity, array(
			'class' => 'AuditBundle\Entity\TipoCampione',
			'expanded' => false,
			'multiple' => false,
			'required' => true,
			'disabled' => $options['disabled'],
			'label' => 'Tipo campione'
		));

		$builder->add('numero_operazioni_campione', self::integer, array(
			'required' => true,
			'disabled' => $options['disabled'],
			'label' => 'Numero operazioni del campione')
		);

		$builder->add('spesa_certificata', self::importo, array(
			"required" => true,
			'disabled' => $options['disabled'],
			"label" => 'Spesa totale certificata al momento del campionamento',
			"currency" => "EUR",
			"grouping" => true)
		);

		$builder->add('numero_strati_campione', self::integer, array(
			'required' => true,
			'disabled' => $options['disabled'],
			'label' => 'Numero strati del campione')
		);

		$builder->add('passo_campionamento', self::integer, array(
			'required' => false,
			'disabled' => $options['disabled'],
			'label' => 'Passo campionamento')
		);

		$builder->add('spesa_certificata_strato', self::importo, array(
			"required" => true,
			'disabled' => $options['disabled'],
			"label" => 'Spesa totale certificata per ciascun strato (book value)',
			"currency" => "EUR",
			"grouping" => true)
		);

		$builder->add('numero_operazioni_universo', self::integer, array(
			'required' => true,
			'disabled' => $options['disabled'],
			'label' => 'Numero operazioni universo')
		);

		$builder->add('devizione_standard', self::numero, array(
			"required" => true,
			'disabled' => $options['disabled'],
			"label" => 'Deviazione standard dal campione globale')
		);

		$builder->add('soglia_rilevanza', self::numero, array(
			"required" => true,
			'disabled' => $options['disabled'],
			"label" => 'Soglia di rilevanza')
		);

		$builder->add('periodo_da', self::birthday, array(
			'required' => true,
			'disabled' => $options['disabled'],
			'label' => 'Periodo da',
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy')
		);

		$builder->add('periodo_a', self::birthday, array(
			'required' => true,
			'disabled' => $options['disabled'],
			'label' => 'Periodo a',
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy')
		);

		if ($options['disabled'] == false) {
			$builder->add('documento', self::documento_simple, array(
				"label" => false,
				'required' => false,
				"opzionale" => true
			));
		}
		$builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"], 'disabled' => false));
	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(
				array(
					'data_class' => 'AuditBundle\Entity\AuditOperazione',
					'readonly' => false,
		));

		$resolver->setRequired('url_indietro');
	}

}
