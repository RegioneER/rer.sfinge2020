<?php

namespace RichiesteBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InterventoType extends RichiestaType {

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$disabled = $options["readonly"];

		if ($options['is_multiproponente']) {
			$builder->add('proponente', self::entity, array(
				'placeholder' => '-',
				'class' => "RichiesteBundle\Entity\Proponente",
				"label" => "Proponente",
				'choice_label' => "soggetto",
				"choices" => $options["proponenti"]
			));
		}

		$builder->add('indirizzo', self::indirizzo, [
			"label" => false,'disabled' => $disabled
		]);
		$builder->add('email', self::text, array('required' => true, 'disabled' => $disabled, 'label' => 'Email'));
		$builder->add('pec', self::text, array('required' => true, 'disabled' => $disabled, 'label' => 'Email PEC'));
		$builder->add('tel', self::text, array('required' => true, 'disabled' => $disabled, 'label' => 'Telefono'));

		$builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"], 'disabled' => $options['disabled']));
	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'RichiesteBundle\Entity\Intervento',
			'readonly' => false,
			'disabled' => false,
			'url_indietro' => false,
			'dataIndirizzo' => null,
			'is_multiproponente' => null,
			'proponenti' => array(),
			'validation_groups' => function($form) {
				$data = $form->getData();
				if (is_object($data->getIndirizzo()->getStato())) {
					if ($data->getIndirizzo()->getStato()->getDenominazione() == 'Italia') {
						return array("Default", "statoItalia", "persona");
					} else {
						return array("Default", "persona");
					}
				} else {
					return array("Default", "persona");
				}
			}
		));
	}

}
