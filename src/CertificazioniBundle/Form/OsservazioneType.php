<?php

namespace CertificazioniBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Regex;

class OsservazioneType extends CommonType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {     
		$builder->add('osservazioni_8_'.$options['num_asse'], self::textarea, array(
			'required' => false, 
			'disabled' => $options["readonly"] , 
			'label' => 'Osservazione Asse '.$options['num_asse'], 
		));
		$builder->add('pulsanti_'.$options['num_asse'], self::salva, array('disabled' => $options["readonly"], 'label' => false));	
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CertificazioniBundle\Entity\CertificazioneChiusura'
        ));
        
        $resolver->setRequired("readonly");
		$resolver->setRequired("num_asse");
    }
}
