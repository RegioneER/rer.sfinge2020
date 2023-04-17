<?php

namespace SoggettoBundle\Form;

use BaseBundle\Form\CommonType;
use BaseBundle\Validator\Constraints\CfSoggettoConstraint;
use SoggettoBundle\Entity\Soggetto;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class SoggettoGiuridicoType extends CommonType {

	protected $piva_required;
	protected $data_costituzione_required;

	public function __construct() {
		$this->piva_required = false;
		$this->data_costituzione_required = false;
	}

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {

		$read_only = $options["readonly"];
		$disabled = $options["readonly"];

		if ($read_only == true) {
			$attr = array('readonly' => 'readonly');
		} else {
			$attr = array();
		}

        $builder->add('codice_fiscale', self::text, array(
            'required' => true,
            'disabled' => $disabled,
            'label' => 'Codice fiscale',
            'attr' => $attr,
            'constraints' => [
                new NotNull(),
                new NotBlank(),
                new CfSoggettoConstraint([
                    'obbligatorio' => true,
                    'legaleRappresentante' => null,
                ]),
            ]
        ));

        $builder->add('tipo', self::choice, array(
            'choices' => array(
                Soggetto::TESTO_AZIENDA => Soggetto::AZIENDA,
                Soggetto::TESTO_PROFESSIONISTA => Soggetto::PROFESSIONISTA,
                Soggetto::TESTO_COMUNE => Soggetto::COMUNE,
                Soggetto::TESTO_UNIVERSITA => Soggetto::UNIVERSITA,
                Soggetto::TESTO_PERSONA_FISICA => Soggetto::PERSONA_FISICA,
                'Altri soggetti pubblici' => Soggetto::ALTRI,
            ),
            'choices_as_values' => true,
            'placeholder' => '-',
            'disabled' => $disabled,
            'attr' => $attr,
            'required' => true,
            'constraints' => [
                new NotNull(),
                new NotBlank(),
            ]
        ));

		$builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"], 'disabled' => $disabled, 'label_salva' => 'Prosegui >>',));
	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults([
            'readonly' => false,
        ]);
		$resolver->setRequired("readonly");
		$resolver->setRequired("url_indietro");
	}

}
