<?php

namespace SoggettoBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;


class RicercaIncaricatiGestioneManagerPAType  extends RicercaIncaricatiGestioneType {

	public function buildForm(FormBuilderInterface $builder, array $options) {

		parent::buildForm($builder, $options);

		$builder->add('incarico', self::entity, array(
			'class' => 'SoggettoBundle:TipoIncarico',
			'query_builder' => function (EntityRepository $er) {
						return $er->createQueryBuilder('tp')
						->orderBy('tp.descrizione', 'ASC')
		                ->where("tp.codice IN ('LR','DELEGATO')" );  },
			'required' => false,
		));						
	}
	
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'SoggettoBundle\Form\Entity\RicercaIncaricatiGestione',
		));
	}

}
