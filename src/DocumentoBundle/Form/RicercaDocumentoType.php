<?php

namespace DocumentoBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class RicercaDocumentoType extends CommonType {


	private $ruoli;
	/**
	 * RicercaDocumentoAdminType constructor.
	 */
	public function __construct()
	{

	}

	public function getName() {
        return "ricerca_documento";
    }

	public function buildForm(FormBuilderInterface $builder, array $options) {

		parent::buildForm($builder, $options);

		$builder->add('id', self::integer, array('required' => false, 'label' => 'Id'));
		$builder->add('nome', self::text, array('required' => false, 'label' => 'Nome file'));

		$builder->add('tipologia', self::entity, array(
			'class' => 'DocumentoBundle:TipologiaDocumento',
			'choice_label' => 'descrizione',
			'required' => false
		));

	}
	
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'DocumentoBundle\Form\Entity\RicercaDocumento',
		));
	}

}
