<?php

namespace AttuazioneControlloBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class DurcType extends CommonType {

	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder->add('dati_variati', self::choice, array(
			'label' => 'I dati relativi all’impresa sono variati rispetto a quanto dichiarato in fase di presentazione della domanda?',
			'choices_as_values' => true,
			'choices' => array(
				'Si' => true, 
				'No' => false
			),
			'required' => true,
			'constraints' => array(new NotNull())
		));

		$builder->add('email_pec', self::text, array(
			'label' => 'PEC ',
			'required' => true,
			'constraints' => array(new NotNull())
				)
		);

		$builder->add('impresa_iscritta_inps', self::choice, array(
			'label' => "L'imprese è iscritta all'INPS ?",
			'choices_as_values' => true,
			'choices' => array(
				'Si' => true, 
				'No' => false
			),
			'required' => true,
			//'disabled' => true,
			'constraints' => array(new NotNull())
		));

		$builder->add('matricola_inps', self::text, array(
			'label' => 'Numero di matricola INPS',
			'required' => true,
			'constraints' => array(new NotNull(array('groups' => array('dati_inps'))))
				)
		);

		$builder->add('impresa_iscritta_inps_di', self::text, array(
			'label' => "L'impresa è iscritta all'INPS di",
			'required' => true,
			'constraints' => array(new NotNull(array('groups' => array('dati_inps'))))
				)
		);

		$builder->add('impresa_iscritta_inail', self::choice, array(
			'label' => "L'imprese è iscritta all'INAIL ?",
			'choices_as_values' => true,
			'choices' => array(
				'Si' => true, 
				'No' => false
			),
			'required' => true,
			//'disabled' => true,
			'constraints' => array(new NotNull())
				)
		);

		$builder->add('numero_codice_ditta_impresa_assicurata', self::text, array(
			'label' => "L'impresa è assicurata con codice ditta n.",
			'required' => true,
			'constraints' => array(new NotNull(array('groups' => array('dati_inail'))))
				)
		);

		$builder->add('impresa_iscritta_inail_di', self::text, array(
			'label' => "L'impresa è iscritta all'INAIL di",
			'required' => true,
			'constraints' => array(new NotNull(array('groups' => array('dati_inail'))))
				)
		);

		$builder->add('ccnl', self::text, array(
			'label' => "Contratto collettivo nazionale di lavoro (C.C.N.L) applicato dall'impresa",
			'required' => true,
			'constraints' => array(new NotNull(array('groups' => array('ccnl'))))
				)
		);


		$builder->add("pulsanti", self::salva_indietro, array("url" => $options["url_indietro"]));
		
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Entity\DurcPagamento',
			'istruttoria' => false,
			'validation_groups' => function(\Symfony\Component\Form\FormInterface $form) {
				
			/**
			 * gdisparti
			 * in pratica è uscito fuori che ad esempio un professionista non ha un DURC
			 * per cui gestiamo il fatto valutando i due flag inps/inail
			 * in caso di assenza di DURC si flaggano a no e non vengono richiesti i dati relativi
			 */
				$groups = array('Default');
				$durcPagamento = $form->getData();
				if(!is_null($durcPagamento)){
					$iscrittaInail = $durcPagamento->getImpresaIscrittaInail();
					$iscrittaInps = $durcPagamento->getImpresaIscrittaInps();
					
					if($iscrittaInps == true){
						$groups[] = 'dati_inps';
					}
					
					if($iscrittaInail == true){
						$groups[] = 'dati_inail';
					}
						
					if($iscrittaInps == true || $iscrittaInail == true){
						$groups[] = 'ccnl';
					}
				}
				
				return $groups;				
			}
		));
		
		
		
		$resolver->setRequired("url_indietro");
	}

}
