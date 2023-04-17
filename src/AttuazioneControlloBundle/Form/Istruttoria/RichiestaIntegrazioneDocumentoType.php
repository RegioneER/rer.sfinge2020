<?php

namespace AttuazioneControlloBundle\Form\Istruttoria;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class RichiestaIntegrazioneDocumentoType extends CommonType {
    
	public function buildForm(FormBuilderInterface $builder, array $options)
    {
		 
		$builder->add('integrazione', self::checkbox, array(
            'label'=> false,
        ));
		
		$builder->add('nota_integrazione', self::textarea, array(
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
            'data_class' => 'AttuazioneControlloBundle\Entity\DocumentoPagamento'
        ));
    }
}

