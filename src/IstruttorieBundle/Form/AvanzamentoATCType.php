<?php

namespace IstruttorieBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class AvanzamentoATCType extends CommonType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('ammissibilita_atto', self::choice, array(
				'choices' => array('Ammissibile' => true, 'Non Ammissibile' => false),
				'choices_as_values' => true,
				'choice_value' => array($this, "mapping"),
				'required' => true,
				'expanded' => true,
				'label' => 'Atto',
			)
		);
		
		$builder->add('concessione', self::choice, array(
				'choices' => array('Si' => true, 'No' => false),
				'choices_as_values' => true,
				'choice_value' => array($this, "mapping"),
				'required' => true,
				'expanded' => true,
				'label' => 'Concessione',
			)
		);	
		
		$builder->add('contributo_ammesso', self::importo, array(
			'required' => false,
			'label' => 'Contributo concesso',
			"currency" => "EUR",
			"grouping" => true,
		));
		
		$builder->add('data_contributo', self::birthday, array(
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',
			'required' => false,
			'label' => 'Data del contributo concesso',
		));
		
		$builder->add('impegno_ammesso', self::importo, array(
			'required' => false,
			'label' => 'Importo impegnato',
			"currency" => "EUR",
			"grouping" => true,
		));
		
		$builder->add('data_impegno', self::birthday, array(
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',
			'required' => false,
			'label' => "Data dell'impegno",
		));
		
		$builder->add('atto_ammissibilita_atc', self::entity, array('required' => false,
			'label' => 'Atto di ammissibilità',	
			'class' => 'SfingeBundle\Entity\Atto',
			'choices' => $options["atti"],
			'placeholder'=> "-",
		));
		
		$builder->add('atto_concessione_atc', self::entity, array('required' => false,
			'label' => 'Atto di concessione',	
			'class' => 'SfingeBundle\Entity\Atto',
			'choices' => $options["atti"],
			'placeholder'=> "-",
		));		

		$builder->add('data_avvio_progetto', self::birthday, array(
            "label" => "Data inizio progetto",
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',
            'constraints' => array(new NotNull(array('groups' => 'avanzamento_atc')))
        ));
		
		$builder->add('data_termine_progetto', self::birthday, array(
            "label" => "Data termine progetto",
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',
            'constraints' => array(new NotNull(array('groups' => 'avanzamento_atc')))
        ));
		
		// se SI, il beneficiario non potrà inserire fatture con data precedente a questa data
		$builder->add('data_inizio_vincolante', self::choice, array(
			'choice_value' => array($this, "mapping"),
			'label' => 'Data avvio vincolante', 
            'choices'  => array('Si' => true,'No' => false),'choices_as_values' => true, 'expanded' => false, 'placeholder' => '-', 'constraints' => array(new NotNull(array('groups' => 'avanzamento_atc')))
		));
		
		// se PUBBLICO, non sarà più attivo il controllo su iban e non sarà obbligatorio in fase di rendicontazione
		$builder->add('tipologia_soggetto', self::choice, array( 
			'label' => 'Tipologia soggetto', 
            'choices'  => array('Pubblico' => 'PUBBLICO','Privato' => 'PRIVATO'),'choices_as_values' => true, 'expanded' => false, 'placeholder' => '-', 'constraints' => array(new NotNull(array('groups' => 'avanzamento_atc')))
		));		

		$builder->add('atto_modifica_concessione_atc', self::entity, array('required' => false,
			'label' => 'Atto modifica di concessione',	
			'class' => 'SfingeBundle\Entity\Atto',
			'choices' => $options["atti"],
			'placeholder'=> "-",
		));
		
		$builder->add('pulsanti', 'IstruttorieBundle\Form\IstruttoriaButtonsType', array("url" => $options["url_indietro"], "label" => false, "valida" => !$options['invalidabile'], "invalida" => $options['invalidabile']));
	}
	
	public function mapping($currentChoiceKey) {
		if (is_null($currentChoiceKey)) { return ''; }
		return $currentChoiceKey ? '1' : '0';
	}	

    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);

		$resolver->setDefaults(array(
			'data_class' => 'IstruttorieBundle\Entity\IstruttoriaRichiesta',
            'validation_groups' => function ($form) {
                $data = $form->getData();
                if (is_object($data->getRichiesta()->getProcedura())) {
                    if ($data->getRichiesta()->getProcedura()->isSezioneIstruttoriaCup()) {
                        return ["avanzamento_atc"];
                    }
                }
            },
		));
		
		$resolver->setRequired("url_indietro");
		$resolver->setRequired("atti");	
		$resolver->setRequired("invalidabile");	
    }

}






