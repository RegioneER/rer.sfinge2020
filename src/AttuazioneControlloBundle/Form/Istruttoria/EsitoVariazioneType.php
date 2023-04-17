<?php

namespace AttuazioneControlloBundle\Form\Istruttoria;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EsitoVariazioneType extends CommonType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('esito_istruttoria', self::choice, array(
			'choice_value' => array($this, "mapping"),
			'label' => 'Esito finale',
			'choices' => array('Ammessa' => true, 'Non ammessa' => false),
			'choices_as_values' => true,
			'expanded' => true,
			'required' => true,
			'placeholder' => false,
			'disabled' => $options['disabled_campi'],
			'constraints' => array(new \Symfony\Component\Validator\Constraints\NotNull())
		));


		$builder->add('note_istruttore', self::textarea, array(
			'label' => 'Note istruttore',
			'required' => false,
			'disabled' => $options['disabled_campi'],
			'attr' => array('rows' => 6)
		));

		$builder->add('pulsanti', self::valida_invalida_indietro, array(
			"url" => $options["url_indietro"], 
			"label" => false, 
			"disabled_invalida" => $options["disabled_invalida"], 
			"disabled_valida" => $options["disabled_valida"], 
		));
	}

	public function configureOptions(OptionsResolver $resolver) {
		parent::configureOptions($resolver);

		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Entity\VariazioneRichiesta'
		));

		$resolver->setRequired("url_indietro");
		$resolver->setRequired("disabled_invalida");
		$resolver->setRequired("disabled_valida");
		$resolver->setRequired("disabled_campi");
	}

}
