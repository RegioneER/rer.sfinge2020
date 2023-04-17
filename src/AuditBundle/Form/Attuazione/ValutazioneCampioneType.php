<?php

namespace AuditBundle\Form\Attuazione;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class ValutazioneCampioneType extends CommonType {

	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder->add('esito', self::choice, array(
			'choices' => array('Positivo' => true, 'Negativo' => false),
			'choices_as_values' => true,
            'choice_value' => array($this, "mapping"), 
			'required' => true,
			'expanded' => true,
			'multiple' => false,
			'label' => 'Giudizio',
            'constraints' => array(new NotNull())
		));

		$builder->add('follow_up', self::entity, array(
			'class' => "AuditBundle\Entity\FollowUp",
			'label' => 'Follow up',
			'required' => true,
			'expanded' => true,
			'multiple' => false,
            'constraints' => array(new NotNull())            
		));
		
		$builder->add('importo_irregolare_pre_contr', self::importo, array(
			'required' => true,
			'disabled' => false,
			'label' => 'Importo irregolare pre-contraddittorio'
		));

		$builder->add('spesa_publ_irregolare_pre_contr', self::importo, array(
			'required' => true,
			'disabled' => false,
			'label' => 'Spesa pubblica irregolare pre-contraddittorio'
		));
		
		$builder->add('importo_irregolare', self::importo, array(
			'required' => true,
			'disabled' => false,
			'label' => 'Importo irregolare'
		));
		
		$builder->add('spesa_pub_irregolare', self::importo, array(
			'required' => true,
			'disabled' => false,
			'label' => 'Spesa pubblica irregolare'
		));

		$builder->add('decurtazione_finanziaria', self::importo, array(
			'required' => true,
			'disabled' => false,
			'label' => 'Proposta decurtazione finaziaria',
            'constraints' => array(new NotNull())            
		));

		$builder->add('note', self::textarea, array(
			'required' => false,
			'disabled' => false,
			'label' => 'Note'
		));

		$builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"], 'disabled' => false));
	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(
				array(
					'data_class' => 'AuditBundle\Entity\AuditCampione',
					'readonly' => false,
		));

		$resolver->setRequired('url_indietro');
	}

}
