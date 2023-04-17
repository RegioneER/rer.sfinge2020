<?php

namespace IstruttorieBundle\Form;


use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class IntegrazioneDocumentoType extends  CommonType{
	public function buildForm(FormBuilderInterface $builder, array $options)
    {
		 
		$builder->add('selezionato', self::checkbox, array(
            'label'=> false,
        ));
		
		$builder->add('nota', self::textarea, array(
				'label' => 'Nota',
				'required' => false
			)
		);
	}
	
	/**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'IstruttorieBundle\Entity\IntegrazioneIstruttoriaDocumento'
        ));
    }
}
