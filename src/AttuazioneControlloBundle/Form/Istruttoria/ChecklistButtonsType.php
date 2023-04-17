<?php

namespace AttuazioneControlloBundle\Form\Istruttoria;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;


class ChecklistButtonsType extends \BaseBundle\Form\SalvaIndietroType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
		parent::buildForm($builder, $options);

		if ($options["enable_valida_liq_controllo"]) {
            $builder->add('pulsante_valida_liq_controllo', self::submit, array('label' => "Salva e valida come liquidabile per controllo in loco"));
        }elseif ($options["enable_valida_liq"]) {
            $builder->add('pulsante_valida_liq', self::submit, array('label' => "Salva e valida come liquidabile"));
        }elseif ($options["enable_valida"]) {
            $builder->add('pulsante_valida', self::submit, array('label' => "Salva e valida"));
        }
		
        if ($options["enable_valida_non_liq"]) {
            $builder->add('pulsante_valida_non_liq', self::submit, array('label' => "Salva e valida come non liquidabile"));
        }	
		
		if ($options["enable_invalida"]) {
			$builder->add('pulsante_invalida', self::submit, array('label' => "Invalida"));
		}
    }

    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        $resolver->setDefaults(
            array(
                'enable_invalida' => false,
                'enable_valida_liq' => true,
				'enable_valida_liq_controllo' => false,
				'enable_valida_non_liq' => true,
				'enable_valida' => false,
				'label' => false
            ));		
    }

    public function getBlockPrefix()
    {
        return 'checklist_buttons';
    }

}






