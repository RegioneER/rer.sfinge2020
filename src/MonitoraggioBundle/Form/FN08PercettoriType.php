<?php

namespace MonitoraggioBundle\Form;

use MonitoraggioBundle\Form\BaseFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of FN08PercettoriType
 *
 * @author gorlando
 */
class FN08PercettoriType extends BaseFormType {

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder
				->add(
						'cod_locale_progetto', 
						self::text, 
						array(
							'label' => 'Codice locale progetto',
							'disabled' => $options['disabled'],
							'required' => !$options['disabled'],
						)
				)
				->add(
						'cod_pagamento', 
						self::text, 
						array(
							'label' => 'Codice pagamento',
							'disabled' => $options['disabled'],
							'required' => !$options['disabled'],
						)
				)
				->add(
						'tipologia_pag', 
						self::choice, 
						array(
							'label' => 'Tipologia pagamento',
							'disabled' => $options['disabled'],
							'required' => !$options['disabled'],
							"placeholder" => '-',
							'choices_as_values' => true,
                    		"choices" => array(
								"Pagamento" => "P",
								"Rettifica" => "R",
								"Pagamento per trasferimento" => "P-TR",
								"Rettifica per trasferimento" => "R-TR",),
							)
				)
				->add(
						'data_pagamento', 
						self::birthday, 
						array(
							'label' => 'Data pagamento',
							'disabled' => $options['disabled'],
							'required' => !$options['disabled'],
							"widget" => "single_text",
							"input" => "datetime",
							"format" => "dd/MM/yyyy",
						)
				)
				->add(
						'codice_fiscale', 
						self::text, 
						array(
							'label' => 'Codice fiscale',
							'disabled' => $options['disabled'],
							'required' => !$options['disabled'],
						)
				)
				->add(
						'flag_soggetto_pubblico', 
						self::choice, 
						array(
							'label' => 'Soggetto pubblico',
							'disabled' => $options['disabled'],
							'required' => false,
							'choices_as_values' => true,
							'choices' => array(
								'Sì' => 'S',
								'No' => 'N',
							),
						)
				)
				->add(
						'tc40_tipo_percettore', 
						self::entity, 
						array(
							'label' => 'Tipo percettore',
							'disabled' => $options['disabled'],
							'required' => false,
							'class' => 'MonitoraggioBundle\Entity\TC40TipoPercettore',
						)
				)
				->add(
						'importo', 
						self::text, 
						array(
							'label' => 'Importo',
							'disabled' => $options['disabled'],
							'required' => false,
							)
				)
				->add(
						'flg_cancellazione', 
						self::choice, 
						array(
							'label' => 'Cancellato',
							'disabled' => $options['disabled'],
							'required' => false,
							'choices_as_values' => true,
							'choices' => array('Sì' => 'S'),
							'placeholder' => 'No',
						)
				)
				->add(
						'submit', 
						self::salva_indietro, 
						array(
							"url" => $options["url_indietro"],
							'disabled' => false,
						)
				);
	}

	/**
	 * {@inheritdoc}
	 */
	public function configureOptions(OptionsResolver $resolver) {
		parent::configureOptions($resolver);
	}

}
