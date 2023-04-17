<?php

namespace AttuazioneControlloBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * Description of DocumentoPrototipoType
 *
 * @author vincenzodamico
 */
class DocumentoPrototipoType extends CommonType {

	public function buildForm(FormBuilderInterface $builder, array $options) {
		
		$builder->add('documento_file', self::documento, array(
			"label" => false,
			"lista_tipi" => $options["lista_tipi"]
		));

		$builder->add("submit", "Symfony\Component\Form\Extension\Core\Type\SubmitType", array("label" => "Carica"));
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Entity\DocumentoPrototipo',
		));
		$resolver->setRequired("lista_tipi");
		$resolver->setRequired("url_indietro");
	}

}
