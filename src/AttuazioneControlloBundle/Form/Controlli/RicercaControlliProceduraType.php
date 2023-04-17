<?php

namespace AttuazioneControlloBundle\Form\Controlli;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use SfingeBundle\Entity\Procedura;
use SfingeBundle\Entity\Asse;
use SfingeBundle\Entity\Azione;
use SfingeBundle\Entity\Atto;
use AttuazioneControlloBundle\Form\Entity\Controlli\RicercaControlliProcedura;

class RicercaControlliProceduraType extends CommonType {

	public function buildForm(FormBuilderInterface $builder, array $options) {
		
		$builder->add('procedura', self::entity, array(
			'class' => Procedura::class,
			'placeholder' => '-',
			'required' => false,
			'label' => 'Procedura'
		));
		
		$builder->add('atto', self::entity, array(
			'class' => Atto::class,
			'placeholder' => '-',
			'required' => false,
			'label' => 'Atto',
			'query_builder' => function(\SfingeBundle\Entity\AttoRepository $er) {
				return $er->createQueryBuilder('atto')
								->join('SfingeBundle:Procedura', 'procedura', 'with', 'atto = procedura.atto');
			},
			'choice_label' => 'numero',
		));

		$builder->add('asse', self::entity, array(
			'class' => Asse::class,
			'required' => false,
			'placeholder' => '-',
			'label' => 'Asse'
		));
		
		$builder->add('azione', self::entity, array(
			'class' => Azione::class,
			'placeholder' => '-',
			'required' => false,
			'label' => 'Azione'
		));
		
	}
	
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => RicercaControlliProcedura::class,
		));
	}

}
