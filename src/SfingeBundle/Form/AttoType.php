<?php

namespace SfingeBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AttoType extends CommonType {
	protected $service_container;

	public function __construct($service_container) {
		$this->service_container = $service_container;
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$read_only = $options["readonly"];
		$disabled = $options["readonly"];

		if ($read_only == true) {
			$attr = array('readonly' => 'readonly');
		} else {
			$attr = array();
		}

		$builder->add('numero', self::text, array('required' => true, 'disabled' => $disabled, 'label' => 'Numero', 'attr' => $attr));
		$builder->add('titolo', self::text, array('required' => true, 'disabled' => $disabled, 'label' => 'Titolo', 'attr' => $attr));
		$builder->add('tipo_atto', self::entity,  array('class' => 'SfingeBundle\Entity\TipoAtto',
			'choice_label' => function ($atto) {
		        return $atto->getCodice().' - '.substr($atto->getDescrizione(),0,89);
		    },
			'placeholder' => '-',
			'required' => true,
			'label' => 'Codice Atto',
			'disabled' => $disabled,
			'attr' => $attr,
		));
		
                if(\is_null($builder->getData()->getDocumentoAtto())){
                    $builder->add('documento_atto', self::documento, array('label' => false, 'disabled' => $disabled, "tipo"=> 'ATTO_AMMINISTRATIVO', 'attr' => $attr));
                }	

		$em = $this->service_container->get('doctrine')->getManager();
		
		$builder->add('dirigente_responsabile', self::entity, array(
			'disabled' => $disabled,
			'label' => 'Dirigente Responsabile',
			'attr' => $attr,
			'class' => 'AnagraficheBundle\Entity\Persona',
			'choices' => $em->getRepository("AnagraficheBundle\Entity\Persona")->getDirigenti(),
			'choice_label' => "nomeCognome",
			'placeholder'=>"Selezionare un dirigente",
			'required' => true,
		));

		$builder->add('data_pubblicazione', self::birthday, array(
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',
			'disabled' => $disabled,
			'label' => 'Data di pubblicazione',
			'attr' => $attr,
			'required' => true
		));
		
		$builder->add('procedura', self::entity,  array(
			'class' => 'SfingeBundle\Entity\Procedura',
			'placeholder' => '-',
			'required' => false,
			'label' => 'Procedura',
			'disabled' => ($disabled || $options['readonly_procedura']),
			'attr' => $attr,
		));		


		$builder->add('pulsanti', self::salva_indietro, array("url"=>$options["url_indietro"], 'disabled' => $disabled));

	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'SfingeBundle\Entity\Atto',
			'readonly' => false,
			"mostra_indietro" => true,
                        "readonly_procedura" => false
		));

		$resolver->setRequired("readonly");
		$resolver->setRequired("url_indietro");
	}

}
