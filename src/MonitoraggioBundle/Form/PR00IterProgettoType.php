<?php

namespace MonitoraggioBundle\Form;

use MonitoraggioBundle\Form\BaseFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Description of PR00IterProgettoType
 *
 * @author gorlando
 */
class PR00IterProgettoType extends BaseFormType {
	
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
						'tc46_fase_procedurale', 
						self::entity, 
						array(
							'label' => 'Fase procedurale',
							'disabled' => $options['disabled'],
							'required' => false,
							'class' => 'MonitoraggioBundle\Entity\TC46FaseProcedurale',
						)
				)
				->add(
						'data_inizio_prevista', 
						self::birthday, 
						array(
							'label' => 'Data inizio prevista',
							'disabled' => $options['disabled'],
							'required' => !$options['disabled'],
							"widget" => "single_text",
							"input" => "datetime",
							"format" => "dd/MM/yyyy",
						)
				)
				->add(
						'data_inizio_effettiva', 
						self::birthday, 
						array(
							'label' => 'Data inizio effettiva',
							'disabled' => $options['disabled'],
							'required' => !$options['disabled'],
							"widget" => "single_text",
							"input" => "datetime",
							"format" => "dd/MM/yyyy",
						)
				)
				->add(
						'data_fine_prevista', 
						self::birthday, 
						array(
							'label' => 'Data fine prevista',
							'disabled' => $options['disabled'],
							'required' => !$options['disabled'],
							"widget" => "single_text",
							"input" => "datetime",
							"format" => "dd/MM/yyyy",
						)
				)
				->add(
						'data_fine_effettiva', 
						self::birthday, 
						array(
							'label' => 'Data fine effettiva',
							'disabled' => $options['disabled'],
							'required' => !$options['disabled'],
							"widget" => "single_text",
							"input" => "datetime",
							"format" => "dd/MM/yyyy",
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
