<?php

namespace IstruttorieBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NotaRispostaComunicazioneType extends CommonType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
    {
			
		$builder->add('testo', self::textarea, array(
				'label' => 'Testo nota di risposta',
				'required' => true,
				'constraints' => array(new \Symfony\Component\Validator\Constraints\NotBlank())
			)
		);
		
		$builder->add('pulsanti', 'BaseBundle\Form\SalvaIndietroType', array("url" => $options["url_indietro"], "label" => false, "disabled" => false));				
		
	}
	
	/**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'IstruttorieBundle\Entity\RispostaComunicazioneEsitoIstruttoria'
        ));
		
		$resolver->setRequired("url_indietro");
    }
}
