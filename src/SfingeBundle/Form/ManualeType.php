<?php

namespace SfingeBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ManualeType extends CommonType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {

		$disabled = $options["read_only"];
                $builder->add('documento_file', "DocumentoBundle\Form\Type\DocumentoFileType", array("lista_tipi"=> $options['lista_tipi'], 'label' => false, 'disabled' => $disabled));

		$builder->add('descrizione', self::textarea, array('required' => true, 'label' => 'Descrizione', 'disabled' => $disabled));
		
		$builder->add('submit', self::salva_indietro, array('label' => false, 'url' => $options['url_indietro'], 'disabled' => $disabled));
	}
	
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'readonly' => false,
                        'data_class' => "SfingeBundle\Entity\Manuale",
		));
		$resolver->setRequired("readonly");
                $resolver->setRequired("lista_tipi");
                $resolver->setRequired("url_indietro");
	}

}
