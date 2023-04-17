<?php

namespace SfingeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RicercaRichiestaProtocolloType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
    {
		parent::buildForm($builder, $options);
		$builder->add('registro_pg', ChoiceType::class,
            ['required' => false, 'label' => 'Registro', 'choices' => ['PG' => 'PG', 'CR' => 'CR',],]);
		$builder->add('anno_pg', TextType::class,
            ['required' => false, 'label' => 'Anno']);
		$builder->add('num_pg', TextType::class,
            ['required' => false, 'label' => 'Numero']);
	}
	
	public function configureOptions(OptionsResolver $resolver)
    {
		$resolver->setDefaults([
            'data_class' => 'SfingeBundle\Form\Entity\RicercaRichiestaProtocollo',
        ]);
	}
}
