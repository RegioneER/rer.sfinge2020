<?php

namespace AttuazioneControlloBundle\Form\Istruttoria;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ValutazioneChecklistPagamentoStandardType extends CommonType {

	public function buildForm(FormBuilderInterface $builder, array $options) {
		
		$builder->add('valutazioni_elementi', self::collection, array(
			'entry_type' => "AttuazioneControlloBundle\Form\Istruttoria\ValutazioneElementoChecklistPagamentoType",
			'allow_add' => false,
			"label" => false,
			"entry_options" => array(),
			"disabled" => $options["fields_disabled"]
		));
		
		if($options['enable_invalida']){
			$builder->add('notaInvalidazione', self::textarea, array(
				'label' => 'Motivo invalidazione',
				'constraints' => array(new \Symfony\Component\Validator\Constraints\NotNull),
				'required' => false
				//'disabled' => $options["to_do"]
			));
		}
		
		$buttonsOptions = array(
			'url' => $options["url_indietro"],
			'enable_invalida' => $options['enable_invalida'],
			'enable_valida' => $options['enable_valida'],
            'enable_valida_liq' => $options['enable_valida_liq'],
			'enable_valida_liq_controllo' => $options['enable_valida_liq_controllo'],
			'enable_valida_non_liq' => $options['enable_valida_non_liq'],
			'mostra_salva' => $options['mostra_salva']
		);
				
		// nasconderemo i pulsanti quando l'istruttoria sarà conclusa..ovvero quando c'è il mandato (al momento)
		$builder->add('pulsanti', 'AttuazioneControlloBundle\Form\Istruttoria\ChecklistButtonsType', $buttonsOptions);


	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Entity\Istruttoria\ValutazioneChecklistPagamento',
			'enable_pulsanti' => true,
		));

		$resolver->setRequired("url_indietro");
		
		$resolver->setRequired("enable_invalida");
		$resolver->setRequired("enable_valida");
		$resolver->setRequired("enable_valida_liq");
		$resolver->setRequired("enable_valida_liq_controllo");
		$resolver->setRequired("enable_valida_non_liq");
		$resolver->setRequired("mostra_salva");
		$resolver->setRequired("fields_disabled");
		$resolver->setRequired("enable_pulsanti"); 
	}

}
