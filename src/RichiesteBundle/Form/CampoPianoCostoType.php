<?php

namespace RichiesteBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Form\Extension\Core\DataTransformer\NumberToLocalizedStringTransformer;

class CampoPianoCostoType extends CommonType {

	public function buildForm(FormBuilderInterface $builder, array $options) {

		for ($i = 1; $i <= $options['annualita']; $i++) {
			$builder->add('importo_anno_' . $i, self::importo, array("required" => false,
				"label" => $options['labels_anno']['importo_anno_' . $i], "scale" => 2,
				"currency" => "EUR",
				"grouping" => true,
                'disabled' => $options['disabled'] || $options['disable_piano']));
		}
        
        if ($options['totale']) {
			$builder->add('importo_totale', self::importo, array("required" => false,
				"label" => "Totale", "scale" => 2,
				"currency" => "EUR",
				"grouping" => true));            
        }
        
        if ($options['descrizione']) {
			$builder->add('descrizione', null, array("label" => "Descrizione"));            
        }        
	}

	/**
	 * @param OptionsResolver $resolver
	 */
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'RichiesteBundle\Entity\VocePianoCosto',
			'constraints' => array(new Valid()),
            'totale' => false,
            'descrizione' => false,
            'disable_piano' => false
		));
		
		$resolver->setRequired("annualita");
		$resolver->setRequired("labels_anno");
		
	}

}
