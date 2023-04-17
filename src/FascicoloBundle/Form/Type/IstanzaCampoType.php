<?php

namespace FascicoloBundle\Form\Type;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;

class IstanzaCampoType extends CommonType {
	
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $campo = $options["attr"]["campo"];
		$dato = $options["attr"]["dato"];
		$container = $options["attr"]["container"];
		
		$servizio = $container->get("fascicolo.tipo.".$campo->getTipoCampo()->getCodice());
		$type = $servizio->getType();
		$options_campo = $servizio->getTypeOptions($campo, $dato);
		$builder->add('valore', $type, $options_campo);
    }
}