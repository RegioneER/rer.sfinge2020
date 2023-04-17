<?php

namespace IstruttorieBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EsitoFinaleType extends CommonType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('esito', self::entity, array(
				'class' => 'IstruttorieBundle\Entity\EsitoIstruttoria',
				'choices' => $options["scelte_esito"],
				'placeholder' => '-',
				'required' => true,
				'label' => 'Esito',
			)
		);
		
        $builder->add('data_verbalizzazione', self::birthday, array(
				'widget' => 'single_text',
				'input' => 'datetime',
				'format' => 'dd/MM/yyyy',
				'required' => true,
				'label' => 'Data verbalizzazione'));
		

		$builder->add('note', self::textarea, array(
				'label' => 'Note',
				'required' => false
			)
		);

		$builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"]));		
    }

    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
		
		$resolver->setDefaults(array(
			'data_class' => 'IstruttorieBundle\Entity\IstruttoriaRichiesta',
			'validation_groups' => 'esito_finale'
		));
		
		$resolver->setRequired("scelte_esito");
		$resolver->setRequired("url_indietro");
    }

}






