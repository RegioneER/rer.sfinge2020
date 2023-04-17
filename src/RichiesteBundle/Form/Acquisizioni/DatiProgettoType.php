<?php

namespace RichiesteBundle\Form\Acquisizioni;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DatiProgettoType extends \RichiesteBundle\Form\RichiestaType {


    public function getName() {
		return "dati_progetto";
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$builder->add('data_inizio_progetto', self::birthday, array(
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',
			'required' => true,
			'label' => 'Data inizio progetto'
		));
		
		$builder->add('data_fine_progetto', self::birthday, array(
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',
			'required' => true,
			'label' => 'Data fine progetto'
		));
		
        $builder->add('titolo',self::textarea,
            array("label"=>'Titolo (massimo 500 caratteri)', "required"=>false,
            'attr' => array('style' => 'width: 500px', 'rows' => '5'))
            );

        $builder->add('abstract',self::textarea,
            array("label"=>'Abstract: Sintesi del progetto, da pubblicare su web, da cui sia comprensibile in cosa consiste il progetto, gli obiettivi e i risultati attesi(massimo 1300 caratteri)', 
				"required"=>false,
                'attr' => array('style' => 'width: 500px', 'rows' => '10'))
            );

        $builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"], 'disabled' => false));

    }
    
    /*
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'RichiesteBundle\Entity\Richiesta',
            'readonly' => false,
            "validation_groups" => array("procedure_particolari", "ASS_TEC_ACQ")
        ));
        $resolver->setRequired("url_indietro");
    }
}

