<?php

namespace IstruttorieBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class ComunicazioneProgettoType extends CommonType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
    {
		
		$builder->add('testoEmail', self::textarea, array(
				'label' => 'Testo email',
				'required' => true,
				'constraints' => array(new NotNull()),
				'attr' => array('rows' => 6)
			)
		);
		
		$builder->add('rispondibile', self::checkbox, array(
			"label" => "Abilita possibilità di risposta",
			"required" => false,
		));
		
		$builder->add('pulsanti', 'BaseBundle\Form\SalvaInvioIndietroType', array("url" => $options["url_indietro"],  "label" => false, "disabled" => $options["disabled"]));	
		
	}
	
	/**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'IstruttorieBundle\Entity\ComunicazioneProgetto'
        ));
		
		$resolver->setRequired("url_indietro");
		$resolver->setRequired("disabled");
    }
}
