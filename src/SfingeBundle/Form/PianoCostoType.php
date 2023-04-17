<?php

namespace SfingeBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class PianoCostoType extends CommonType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {
        
        $piano = $builder->getData();
        
		$builder->add('ordinamento', self::numero, array(
			'label'=> "Ordine",
			'required' => true,
            'constraints' => array(new NotNull())
		));
        
        $builder->add('codice', self::text, array('required' => true, 
            'label' => 'Codice',
            'constraints' => array(new NotNull())));
        
        $builder->add('titolo', self::text, array('required' => true, 
            'label' => 'Titolo',
            'constraints' => array(new NotNull())));
        
		$builder->add('sezione_piano_costo', self::entity, array(
			'class' => 'RichiesteBundle:SezionePianoCosto',
			'choices' => $piano->getProcedura()->getSezioniPianiCosto(), 
			'required' => true,
			'placeholder' => '-',
            'constraints' => array(new NotNull())
		));        

		$builder->add('tipo_voce_spesa', self::entity, array(
			'class' => 'RichiesteBundle:TipoVoceSpesa',
			// 'choices' => $em->getRepository("RichiesteBundle\Entity\TipoVoceSpesa")->findAll(),
			'required' => true,
			'placeholder' => '-',
            'constraints' => array(new NotNull())
		));

		$builder->add('identificativo_pdf', self::text, array(
			"label" => "Identificativo pdf",
			"required" => true,
            'constraints' => array(new NotNull())
		));
        
		$builder->add('identificativo_html', self::text, array(
			"label" => "Identificativo html",
			"required" => true,
            'constraints' => array(new NotNull())
		));        

        $builder->add('pulsanti', self::salva_indietro, array("url"=>$options["url_indietro"]));
	}
	
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'RichiesteBundle\Entity\PianoCosto',
		));
		// $resolver->setRequired("em");
		$resolver->setRequired("url_indietro");
	}

}
