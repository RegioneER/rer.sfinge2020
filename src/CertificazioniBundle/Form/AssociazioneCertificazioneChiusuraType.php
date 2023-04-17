<?php

namespace CertificazioniBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AssociazioneCertificazioneChiusuraType extends CommonType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('certificazioni', self::entity, array(
			'class' => "CertificazioniBundle\Entity\Certificazione",
			'choices' => $options["em"]->getRepository("CertificazioniBundle\Entity\Certificazione")->getCertificazioniSenzaChiusura($options["id_chiusura"]),
			'label' => false,
			'required' => false,
			'expanded' => true,
			'multiple' => true,
		));

		$builder->add('pulsanti', self::salva_blocca_indietro, array("url" => $options["url_indietro"], "label" => false, "disabled" => false));
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'CertificazioniBundle\Entity\CertificazioneChiusura'
		));

		$resolver->setRequired("url_indietro");
		$resolver->setRequired("em");
		$resolver->setRequired("id_chiusura");
	}

}
