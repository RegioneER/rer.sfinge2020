<?php


namespace RichiesteBundle\Form\IngegneriaFinanziaria;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class IngegneriaFinanziariaBeneficiarioType extends CommonType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {

		$builder->add('beneficiario', self::entity,  array('class' => 'SoggettoBundle\Entity\Soggetto',
			'choice_label' => function ($ooii) {
		        return $ooii->getDenominazione();
		    },
			'placeholder' => '-',
			'required' => true,
			'label' => 'Beneficiario',
			'constraints' => array(new NotNull()),
			'query_builder' => function(\Doctrine\ORM\EntityRepository $er){
					return $er->createQueryBuilder('b')
							->where('b.id = 1026')
							->andWhere('b.codice_fiscale = \'80062590379\''); // regione emilia
			}));	

		$builder->add("submit", self::submit, array("label" => "Salva"));
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'RichiesteBundle\Form\Entity\IngegneriaFinanziaria\ingegneriaFinanziariaBeneficiario'
		));
	}

}
