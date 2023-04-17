<?php

namespace AttuazioneControlloBundle\Form\Istruttoria;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RipartizionePagamentoProponenteType extends CommonType {
	
	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder->add('importo_contributo', self::importo, array('required' => false, 'label' => false));

		if($options['invio']){
			$builder->add('pulsanti', self::salva, array("label" => false, "disabled" => $options['disabled']));		
		}
		//$builder->add('pulsanti', 'IstruttorieBundle\Form\IstruttoriaButtonsType', array("url" => $options["url_indietro"], "label" => false, "disabled" => false, "invalida" => $options["invalida"], "integrazione" => $options["integrazione"], "url_integrazione" => $options["url_integrazione"]));		
		
	}
	
	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Entity\Istruttoria\RipartizioneImportiPagamento',
			'invio' => false
		));	
		
	}	
}
