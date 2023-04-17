<?php

namespace IstruttorieBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class IntegrazioneGestioneType extends CommonType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
    {
			
		$builder->add('testo', self::textarea, array(
				'label' => 'Testo',
				'required' => false	,
				'attr' => array('rows' => 6)
			)
		);
		
		$builder->add('testoEmail', self::textarea, array(
				'label' => 'Testo email',
				'required' => true,
				'constraints' => array(new NotNull()),
				'attr' => array('rows' => 6)
			)
		);
		
		$builder->add('tipologie_documenti_estesi', self::collection, array(
            'entry_type'   => "IstruttorieBundle\Form\IntegrazioneDocumentoType",
			'entry_options'  => array("label" => false),
            'allow_add'    => false,
			'label' => false,

        ));
		
		$builder->add('pulsanti', 'BaseBundle\Form\SalvaInvioIndietroType', array("url" => $options["url_indietro"], "label" => false, "disabled" => false));				
		
	}
	
	/**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'IstruttorieBundle\Entity\IntegrazioneIstruttoria'
        ));
		
		$resolver->setRequired("url_indietro");
    }
}
