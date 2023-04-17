<?php

namespace AttuazioneControlloBundle\Form\Istruttoria;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EsitoPagamentoType extends CommonType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) { 
        $builder->add('esito_istruttoria', self::choice, array(
            'choice_value' => array($this, "mapping"), 
            'label' => 'Esito finale', 
            'choices'  => array('Non ammesso' => false, 'Ammesso' => true), 
            'choices_as_values' => true, 
            'expanded' => true, 
            'required' => true, 
            'placeholder' => false,
            'constraints' => array(new \Symfony\Component\Validator\Constraints\NotNull())));

		$builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"]));		
    }

    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
		
		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Entity\Pagamento'
		));
		
		$resolver->setRequired("url_indietro");
    }

}






