<?php

namespace AttuazioneControlloBundle\Form\Istruttoria;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class DocumentiEsitoIstruttoriaPagamentoType extends CommonType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) { 
		
		$builder->add('descrizione', self::textarea, array(
			"label" => "Descrizione documento",
			'constraints' => array(new NotNull())
		));
		
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
			'data_class' => 'AttuazioneControlloBundle\Entity\Istruttoria\DocumentoEsitoIstruttoria',
		));
		$resolver->setRequired("lista_tipi");
		$resolver->setRequired("url_indietro");
	}

}






