<?php

namespace RichiesteBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class InterventoSedeType extends RichiestaType {

	public function buildForm(FormBuilderInterface $builder, array $options) {

		$anni = array();
		if (!is_null($options['piano_costo'])) {
			$builder->add('piano_costo', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', array(
				'class' => 'RichiesteBundle:PianoCosto',
				'choices' => $options["piano_costo"],
				'choice_label' => 'titolo',
				'required' => true,
				'disabled' => false,
				'placeholder' => '-',
				'constraints' => array(new NotNull())
			));
		}

		$builder->add('descrizione', self::textarea, array(
			'required' => true,
			'disabled' => $options['disabled'],
			'label' => 'Descrizione'
		));

		$builder->add('costo', self::importo, array("required" => true,
			"label" => 'Costo stimato',
			"scale" => 2,
			"currency" => "EUR",
			"grouping" => true,
			'disabled' => $options['disabled']
		));

        $builder->add('annualita', self::choice, array(
			'choices' => \array_flip($options['anni']),
			'required' => true,
			'label' => 'Annualità',
			'choices_as_values' => true,
		));


		$builder->add('pulsanti', self::salva_indietro, array(
			"url" => $options["url_indietro"],
			'disabled' => $options['disabled']));
	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'RichiesteBundle\Entity\InterventoSede',
			'validation_groups' => array('bando61')
		));

		$resolver->setRequired("url_indietro");
		$resolver->setRequired("piano_costo");
		$resolver->setRequired("finestra_temporale");
        $resolver->setRequired("id_procedura");
        $resolver->setRequired("anni");
	}

}
