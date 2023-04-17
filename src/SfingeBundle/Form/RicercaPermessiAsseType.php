<?php

namespace SfingeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use BaseBundle\Form\RicercaType;

class RicercaPermessiAsseType extends RicercaType {

	public function buildForm(FormBuilderInterface $builder, array $options) {

		parent::buildForm($builder, $options);
			
		$builder->add('utente', self::entity,  array(
			'class' => 'SfingeBundle\Entity\Utente',
			'choices' => $this->container->get('doctrine')->getManager()->getRepository("SfingeBundle\Entity\Utente")->cercaUtentiPa(),
			'required' => false,
		));		

		$builder->add('asse', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', array(
			'class' => 'SfingeBundle:Asse',
			'required' => false,
		));

	}
	
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'SfingeBundle\Form\Entity\RicercaPermessiAsse',
		));
	}

}
