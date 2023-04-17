<?php

namespace RichiesteBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RisorsaProgettoType extends RichiestaType {

	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder->add('tipologia_risorsa', self::entity,  array('class' => 'RichiesteBundle\Entity\TipologiaRisorsa',
			'query_builder' => function(\Doctrine\ORM\EntityRepository $er) use ($options) {
								return $er->createQueryBuilder('tf')
										->where(!is_null($options['procedura']) ? 'tf.procedura = '.$options['procedura']->getId() : 'tf.procedura is null ')
										->orderBy('tf.descrizione', 'ASC');
			},
			'choice_label' => function ($tipologia) {
		        return $tipologia->getDescrizione();
		    },
			'placeholder' => '-',
			'required' => true,
			'label' => 'Tipologia',
		));	
		
        $builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"], 'disabled' => $options['disabled']));

	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'RichiesteBundle\Entity\RisorsaProgetto',
			'procedura' => null,
			'disabled' => false,
			'url_indietro' => false
		));

		$resolver->setRequired("url_indietro");
		$resolver->setRequired("disabled");
		$resolver->setRequired("procedura");
	}

}