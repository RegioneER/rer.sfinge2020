<?php

namespace IstruttorieBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\Options;


class IstruttoriaButtonsType extends \BaseBundle\Form\SalvaIndietroType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
		parent::buildForm($builder, $options);

		if ($options["integrazione"] && !is_null('url_integrazione')) {
			$builder->add('pulsante_integrazione', self::link, array('label' => "Richiedi integrazione", 'attr' => array("href" => $options["url_integrazione"])));
		}
        if ($options["valida"]) {
            $builder->add('pulsante_valida', self::submit, array('label' => "Valida"));
        }
		if ($options["invalida"]) {
			$builder->add('pulsante_invalida', self::submit, array('label' => "Invalida", "disabled" => false));
		}

    }

    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        $resolver->setDefaults(
            array(
                'invalida' => false,
                'valida' => true,
				'integrazione' => false,
				'url_integrazione' => null
            ));		
    }

    public function getBlockPrefix()
    {
        return 'istruttorie_buttons';
    }

}






