<?php

namespace AttuazioneControlloBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class DateProgettoPagamentoStandardType extends CommonType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		
		$builder->add('data_avvio_progetto', self::birthday, array(
            "label" => "Data avvio progetto",
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',
			'disabled' => 'disabled'
        ));
		
		$builder->add('data_termine_progetto', self::birthday, array(
            "label" => "Data termine progetto",
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',
			'disabled' => 'disabled'            
        ));
	
		// campo mostrato solo per i SAL
		// gestito dentro l'entity Pagamento
		if($builder->getData()->needDataFineRendicontazioneSal()){
			$builder->add('data_fine_rendicontazione', self::birthday, array(
				"label" => "Data fine rendicontazione SAL",
				'widget' => 'single_text',
				'input' => 'datetime',
				'format' => 'dd/MM/yyyy',            
				'constraints' => array(new NotNull()),
                'disabled' => $options['dataFineDisabilitata']
			));
		}

		// ad oggi c'è solo da salvare l'eventuale data intermedia nei casi di sal
		$submitDisabled = !$builder->getData()->needDataFineRendicontazioneSal();
		$builder->add("pulsanti", self::salva_indietro, array("url" => $options["url_indietro"], "disabled" => $submitDisabled));
		
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Entity\Pagamento',
            'dataFineDisabilitata' => false
		));
		$resolver->setRequired("url_indietro");
		$resolver->setRequired("tipologia");
	}

}
