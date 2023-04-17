<?php

namespace AttuazioneControlloBundle\Form\Controlli;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class VerbaleDeskControlloType extends CommonType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add('controllo_fase_desk', self::choice, [
            'choices' => [
                'su un acconto/stato avanzamento' => 'ACCONTO_SAL',
                'a seguito della presentazione della rendicontazione finale' => 'SALDO',
            ],
            "label" => "Il controllo fase desk è svolto: ",
            'choices_as_values' => true,
            'constraints' => [new NotNull()],
        ]);
		
		
		$builder->add('osservazioni_fase_desk', self::AdvancedTextType, array(
			'disabled' => false,
			'label' => 'Eventuali osservazioni',
			'constraints' => array(new NotNull())));
		

		$builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"]));
	}

	public function configureOptions(OptionsResolver $resolver) {
		parent::configureOptions($resolver);

		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Entity\Controlli\ControlloProgetto'
		));

		$resolver->setRequired("url_indietro");
	}

}
