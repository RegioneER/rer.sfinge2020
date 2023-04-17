<?php

namespace FascicoloBundle\Form\Type;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;

class PaginaType extends CommonType {
	
	
    public function __construct()
    {
    }
	
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$builder->add('titolo', self::text , array('required' => true, 'label' => 'Titolo'));
		$builder->add('alias', self::text, array('required' => true, 'label' => 'Alias'));
		$builder->add('maxMolteplicita', self::integer , array('required' => true, 'label' => 'Max molteplicità'));
		$builder->add('minMolteplicita', self::integer , array('required' => true, 'label' => 'Min molteplicità'));
		$builder->add('callback', self::text , array('required' => false, 'label' => 'Callback validazione'));
		$builder->add('callbackPresenza', self::text , array('required' => false, 'label' => 'Callback presenza'));
		
		if ($options['button']) {
			$builder->add('submit', self::submit, array('label' => 'Salva'));
		}
    }
	
	public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver) {
		parent::configureOptions($resolver);
		$resolver->setDefaults(array(
			'data_class' => 'FascicoloBundle\Entity\Pagina',
			'cascade_validation' => true,
			'button' => false
		));
	}
}