<?php

namespace SfingeBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PermessiProceduraType extends CommonType {

	public function buildForm(FormBuilderInterface $builder, array $options) {
		$data = $builder->getData();

		$read_only = $options["readonly"];
		$disabled = $options["readonly"];
		$em = $options["em"];

		if ($read_only == true) {
			$attr = array('readonly' => 'readonly');
		} else {
			$attr = array();
		}

		$builder->add('utente', self::entity,  array(
			'class' => 'SfingeBundle\Entity\Utente',
			'choices' => $em->getRepository("SfingeBundle\Entity\Utente")->cercaUtentiAssociabiliProcedureAssi(),
			'choice_label' => function ($utente) {                
                return $utente->getPersona()->getCognome().' '.$utente->getPersona()->getNome();
            },
			'placeholder' => '-',
			'required' => true,
			'label' => 'Utente',
			'disabled' => $disabled,
			'attr' => $attr,
		));

		$builder->add('procedura', self::entity,  array(
			'class' => 'SfingeBundle\Entity\Procedura',
			'placeholder' => '-',
			'label' => 'Procedura',
			'disabled' => $disabled,
			'attr' => $attr,
			'required' => true,
		));

		$builder->add('solo_lettura', self::checkbox,  array(
			'label' => 'Solo lettura',
			'disabled' => $disabled,
			'attr' => $attr,
		));

		$builder->add('pulsanti', self::salva_indietro, array("url"=>$options["url_indietro"]));

	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'SfingeBundle\Entity\PermessiProcedura',
			'readonly' => false,
			"mostra_indietro" => true
		));

		$resolver->setRequired("readonly");
		$resolver->setRequired("url_indietro");
		$resolver->setRequired("em");
	}

}

?>
