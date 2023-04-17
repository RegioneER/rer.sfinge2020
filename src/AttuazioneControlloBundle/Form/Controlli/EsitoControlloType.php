<?php

namespace AttuazioneControlloBundle\Form\Controlli;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EsitoControlloType extends CommonType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) { 
		
		$builder->add('esito', self::entity, array(
            'class'   => 'AttuazioneControlloBundle\Entity\Controlli\TipoEsitoControllo',
            'label' => 'Esito finale',
			'choice_label' => 'descrizione',
            'placeholder' => '-',
            'constraints' => array(new \Symfony\Component\Validator\Constraints\NotNull())
        ));
		
		$builder->add('note_esito', self::textarea, array(
			'disabled' => false,
			'required' => false, 
			'label' => 'Note esito',
		));
		
		$builder->add('data_validazione', self::birthday, array(
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',
			'required' => true,
			'disabled' => false,
			'label' => 'Data validazione',
		));

		$builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"]));		
    }

    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
		
		$resolver->setDefaults(array(
			'data_class' => 'AttuazioneControlloBundle\Entity\Controlli\ControlloProgetto'
		));
		
		$resolver->setRequired("url_indietro");
    }

}






