<?php

namespace AttuazioneControlloBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccettazioneContributoType extends CommonType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('datiBancariProponenti', self::collection, array(
			'allow_add' => false,
			'allow_delete' => false,
			'entry_type' => 'AttuazioneControlloBundle\Form\DatiBancariType',
			'label' => false,
		));
		
		$builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"], "label_salva" => $options["label_pulsante"] ));		
    }

    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
		
		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta',
		));
		
		$resolver->setRequired("url_indietro");
		$resolver->setRequired("label_pulsante");
		
    }
	
	public function getName() {
		return "accettazione_contributo";
	}

}






