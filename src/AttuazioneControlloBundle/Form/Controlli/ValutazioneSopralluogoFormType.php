<?php

namespace AttuazioneControlloBundle\Form\Controlli;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ValutazioneSopralluogoFormType extends CommonType {

	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder->add('note_sopralluogo', self::textarea, array(
			'required' => false,
			'disabled' => false,
			'label' => 'Note'));

		$builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"], 'disabled' => false));
	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(
				array(
					'data_class' => 'AttuazioneControlloBundle\Entity\Controlli\ControlloProgetto',
		));
		$resolver->setRequired('url_indietro');
	}

}
