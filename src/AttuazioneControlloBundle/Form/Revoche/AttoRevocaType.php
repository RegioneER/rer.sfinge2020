<?php

namespace AttuazioneControlloBundle\Form\Revoche;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AttoRevocaType extends CommonType {

	public function __construct() {
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$read_only = $options["readonly"];
		$disabled = $options["readonly"];

		if ($read_only == true) {
			$attr = array('readonly' => 'readonly');
		} else {
			$attr = array();
		}

		$builder->add('numero', self::text, array('required' => true, 'disabled' => $disabled, 'label' => 'Numero atto', 'attr' => $attr));
		
		$builder->add('data', self::birthday, array(
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',
			'disabled' => $disabled,
			'label' => 'Data atto',
			'attr' => $attr,
			'required' => true
		)); 
		
		$builder->add('descrizione', self::text, array('required' => true, 'disabled' => $disabled, 'label' => 'Descrizione', 'attr' => $attr));
		
		$builder->add('tipo', self::entity,  array(
			'class' => 'AttuazioneControlloBundle\Entity\Revoche\TipoRevoca',
			'placeholder' => '-',
			'required' => true,
			'label' => 'Tipo revoca',
			'disabled' => $disabled,
			'attr' => $attr,
		));	
		
		$builder->add('tipo_motivazione', self::entity,  array(
			'class' => 'AttuazioneControlloBundle\Entity\Revoche\TipoMotivazioneRevoca',
			'placeholder' => '-',
			'required' => true,
			'label' => 'Motivazione',
			'disabled' => $disabled,
			'attr' => $attr,
		));	
		
		$builder->add('tipo_origine_revoca', self::entity,  array(
			'class' => 'AttuazioneControlloBundle\Entity\Revoche\TipoOrigineRevoca',
			'placeholder' => '-',
			'required' => true,
			'label' => 'Origine della segnalazione',
			'disabled' => $disabled,
			'attr' => $attr,
		));
		
		$builder->add('documento', self::documento, array(
			'label' => false, 
			'required' => true,
			'tipo' => $options['TIPOLOGIA_DOCUMENTO'], 
			'disabled' => $disabled, 
			'opzionale' => $options['documento_opzionale']));
		
		$builder->add('pulsanti', self::salva_indietro, array("url"=>$options["url_indietro"], 'disabled' => $disabled));

	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Entity\Revoche\AttoRevoca',
			'readonly' => false,
			"mostra_indietro" => true,
			'TIPOLOGIA_DOCUMENTO' => '',
			'documento_opzionale' => false,
		));

		$resolver->setRequired("readonly");
		$resolver->setRequired("url_indietro");
	}

}
