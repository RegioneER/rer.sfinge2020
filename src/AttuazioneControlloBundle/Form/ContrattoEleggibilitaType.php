<?php

namespace AttuazioneControlloBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class ContrattoEleggibilitaType extends CommonType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {	
		
		$builder->add('importo_eleggibilita_istruttoria', self::importo, array(
			"label" => "Importo pagato prima del periodo di eleggibilità validato dall'istruttore",
			"required" => false,
			"currency" => "EUR",
			"disabled" => false,
		));			
		
        $builder->add("pulsanti", self::salva_indietro, array("url" => $options["url_indietro"]));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AttuazioneControlloBundle\Entity\Contratto',
        ));
        $resolver->setRequired("url_indietro");
		$resolver->setRequired("tipologieFornitore");
    }
}
