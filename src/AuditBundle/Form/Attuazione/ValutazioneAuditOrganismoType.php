<?php

namespace AuditBundle\Form\Attuazione;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ValutazioneAuditOrganismoType extends CommonType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('valutazione', self::choice, array(
			'choices' => array('1' => 1, '2' => 2, '3' => 3, '4' => 4),
			'choices_as_values' => true, 
            'empty_value' => '',
			'required' => true,
			'expanded' => false, 
			'multiple' => false, 
			'label' => "Valutazione complessiva sull'organismo"
			));

		$builder->add('data_relazione', self::birthday, array(
			'required' => true,
			'disabled' => false,
			'label' => 'Data relazione finale',
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy'));

		$builder->add('note', self::textarea, array(
			'required' => false,
			'disabled' => false,
			'label' => 'Note'));

		$builder->add('osservazioni_rac', self::textarea, array(
			'required' => false,
			'disabled' => false,
			'label' => 'Osservazioni RAC'));

		$builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"], 'disabled' => false));
	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(
				array(
					'data_class' => 'AuditBundle\Entity\AuditOrganismo',
		));

		$resolver->setRequired('url_indietro');
	}

}
