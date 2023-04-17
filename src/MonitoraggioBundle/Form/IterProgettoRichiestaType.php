<?php

namespace MonitoraggioBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use RichiesteBundle\Entity\Richiesta;
use MonitoraggioBundle\Form\Type\IterProgettoType;
use Symfony\Component\Validator\Constraints\Valid;


class IterProgettoRichiestaType extends CommonType {
    /**
     * {@inheritdoc}
     */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('mon_iter_progetti', self::collection, [
			'entry_type' => IterProgettoType::class,
			'label' => false,
			'prototype' => false,
			'error_bubbling' => true,
			'constraints' => [new Valid()],
		]);
		$builder->add('submit', self::salva_indietro, [
			'url' => $options['indietro'],
		]);
	}
		
	/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults([
			'data_class' => Richiesta::class,
		]);
		$resolver->setRequired('indietro');
    }
}