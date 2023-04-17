<?php

namespace ProtocollazioneBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DatiProtocolloType extends \BaseBundle\Form\CommonType {

	public function getName() {
		return "dati_protocollo";
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder->add('registro_pg', self::text, array("label" => "Richiesta di rimborso Numero", "required" => false,
			'attr' => array('style' => 'width: 60px; display: inline !important', 'rows' => '5'))
		);

		$builder->add('anno_pg', self::text, array("label" => false, "required" => true,
			'attr' => array('style' => 'width: 100px; display: inline !important', 'rows' => '10'))
		);

		$builder->add('num_pg', self::text, array("label" => false, "required" => true,
			'attr' => array('style' => 'width: 200px; display: inline !important', 'rows' => '10'))
		);		

		$builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"], 'disabled' => false));
	}

	/*
	 * @param OptionsResolver $resolver
	 */

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'ProtocollazioneBundle\Entity\RichiestaProtocollo',
			'readonly' => false,
		));
		$resolver->setRequired("url_indietro");
	}

}
