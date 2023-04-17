<?php

namespace AttuazioneControlloBundle\Form\Istruttoria;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class EsitoIstruttoriaPagamentoStandardType extends CommonType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) { 
		
		$builder->add('testoEmail', self::textarea, array(
				'label' => 'Testo da inviare tramite pec al beneficiario',
				'required' => true,
				'constraints' => array(new NotNull()),
				'attr' => array('rows' => 8)
			)
		);
		
		$builder->add('noteAllaLiquidazione', self::textarea, array(
				'label' => 'Note alla liquidazione',
				'required' => true,
				//'constraints' => array(new NotNull()),
				'attr' => array('rows' => 8)
			)
		);

		$builder->add('pulsanti', 'BaseBundle\Form\SalvaInvioIndietroType',
				array(
					"url" => $options["url_indietro"],
					"label" => false,
					"disabled"       => $options["disabled"],
					"disabled_invio" => $options["disabled_invio"],
				)
			);	
		
    }

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Entity\Istruttoria\EsitoIstruttoriaPagamento',
			'disabled_invio' => false,
		));
		$resolver->setRequired("url_indietro");
	}

}






