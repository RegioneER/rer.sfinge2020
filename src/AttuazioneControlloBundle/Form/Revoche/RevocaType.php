<?php

namespace AttuazioneControlloBundle\Form\Revoche;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class RevocaType extends CommonType {

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

		$builder->add('atto_revoca', self::entity, array(
			'class' => 'AttuazioneControlloBundle\Entity\Revoche\AttoRevoca',
			'placeholder' => 'Altro',
			'required' => true,
			'label' => 'Numero atto di revoca',
			'disabled' => $disabled,
			'attr' => $attr,
		));

		$builder->add('data_atto', self::birthday, array(
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',
			'disabled' => true,
			'label' => 'Data atto',
			'attr' => $attr,
			'required' => false,
			'mapped' => false
		));

		$builder->add('tipo_revoca', self::text, array(
			'required' => false,
			'disabled' => true,
			'label' => 'Tipo di revoca',
			'attr' => $attr,
			'mapped' => false
		));

		$builder->add('tipo_motivazione', self::text, array(
			'required' => false,
			'disabled' => true,
			'label' => 'Motivazione',
			'attr' => $attr,
			'mapped' => false));

		$builder->add('contributo', self::importo, array(
			"label" => "Contributo da recuperare/ritirare",
			'constraints' => array(new NotNull()),
			'disabled' => $disabled,
			"currency" => "EUR",
			"grouping" => true
		));
		
		$builder->add('contributo_revocato', self::importo, array(
			"label" => "Contributo revocato",
			'constraints' => array(new NotNull()),
			'disabled' => $disabled,
			"currency" => "EUR",
			"grouping" => true
		));
		
		$builder->add('contributo_ada', self::importo, array(
			"label" => "Contributo taglio ada (solo in caso di taglio ada)",
			'disabled' => $disabled,
			"currency" => "EUR",
			"grouping" => true,
			'required' => false,
		));

		$builder->add('tipo_irregolarita', self::entity, array(
			'class' => 'AttuazioneControlloBundle\Entity\Revoche\TipoIrregolaritaRevoca',
			'placeholder' => '-',
			'required' => true,
			'multiple' => true,
			'label' => 'Tipo irregolarità',
			'disabled' => $disabled,
			'attr' => $attr,
		));
		
		$builder->add('specificare', self::textarea, array('required' => false, 'disabled' => $disabled, 'label' => 'Altro specificare', 'attr' => $attr));

		$builder->add('altro', self::textarea, array('required' => false, 'disabled' => $disabled, 'label' => 'Altro', 'attr' => $attr));

		$builder->add('nota_invio_conti', self::textarea, array('required' => false, 'disabled' => $disabled, 'label' => 'Nota invio conti', 'attr' => $attr));

		$builder->add('con_ritiro', self::checkbox, array(
			"label" => "Ritiro",
			"required" => false,
			'disabled' => $disabled,
		));

		$builder->add('con_recupero', self::checkbox, array(
			"label" => "Recupero",
			"required" => false,
			'disabled' => $disabled,
		));

		$builder->add('invio_conti', self::checkbox, array(
			"label" => "Invio nei conti",
			"required" => false,
			'disabled' => $disabled,
		));
		
		$builder->add('taglio_ada', self::checkbox, array(
			"label" => "Taglio AdA",
			"required" => false,
			'disabled' => $disabled,
		));
		
		$builder->add('articolo_137', self::checkbox, array(
			"label" => "importi sospesi art. 137, co 2",
			"required" => false,
			'disabled' => $disabled,
		));


		$builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"], 'disabled' => $disabled, 'mostra_indietro' => $options["mostra_indietro"]));
	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Entity\Revoche\Revoca',
			'readonly' => false,
			"mostra_indietro" => true
		));

		$resolver->setRequired("readonly");
		$resolver->setRequired("url_indietro");
	}

}
