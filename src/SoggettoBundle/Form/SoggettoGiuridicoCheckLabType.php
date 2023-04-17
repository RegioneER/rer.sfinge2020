<?php

namespace SoggettoBundle\Form;

use BaseBundle\Form\CommonType;
use SoggettoBundle\Entity\Soggetto;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SoggettoGiuridicoCheckLabType extends CommonType {

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

		$disabled = $options["readonly"];

        $builder->add('codice_fiscale', self::hidden, array(
            'data' => $options['data']['codice_fiscale']
        ));

        $builder->add('tipo', self::hidden, array(
            'data' => Soggetto::UNIVERSITA
        ));

		$builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"], 'disabled' => $disabled, 'label_salva' => 'Prosegui >>',));
	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(
				array(
					'readonly' => false,
		));
		$resolver->setRequired("readonly");
		$resolver->setRequired("url_indietro");
		$resolver->setRequired("data");
	}

}
