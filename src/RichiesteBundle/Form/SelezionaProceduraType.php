<?php

namespace RichiesteBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class SelezionaProceduraType extends CommonType {


	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder
		->add('procedura', self::entity, array(
			'class' => 'SfingeBundle\Entity\Procedura',
			'expanded' => true,
			'multiple' => false,
			'required' => true,
			'label' => false,
			'query_builder' => function(EntityRepository $er) use($options){
				$q = $er->createQueryBuilder('p');
				return $q->where("p instance of {$options['classeProcedura']}");
			}
		))
		->add('pulsanti', self::salva_indietro, array(
			"url" => $options["url_indietro"], 
			'disabled' => false
		));
	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'readonly' => false,
			'url_indietro' => false,
			'data_class' => 'RichiesteBundle\Entity\Richiesta',
		))
		->setRequired("classeProcedura");
	}

}
