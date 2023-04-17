<?php

namespace RichiesteBundle\Form\ProceduraPA;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;


class BeneficiarioType extends CommonType
{
    /**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('soggetto', self::entity,  array(
            'class' => 'SoggettoBundle\Entity\Soggetto',
			'choice_label' => function ($ooii) {
		        return $ooii->getDenominazione();
		    },
			'required' => true,
			'label' => 'Beneficiario',
			'constraints' => array(new NotNull()),
			'query_builder' => function( EntityRepository $er){
					return $er->createQueryBuilder('b')
							->where('b.id = 1026')
							->andWhere("b.codice_fiscale = '80062590379'"); // regione emilia
			}));	

		$builder->add("submit", self::submit, array("label" => "Avanti"));
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
            'data_class' => 'RichiesteBundle\Entity\Proponente',            
		));
	}
}
