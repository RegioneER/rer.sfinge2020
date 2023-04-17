<?php

namespace FascicoloBundle\Form\Type;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class FrammentoType extends CommonType {
	
	private $modifica;
	
    public function __construct($modifica=false)
    {		
		$this->modifica = $modifica;
    }
	
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$builder->add('tipoFrammento', self::entity , array('class' => 'FascicoloBundle\Entity\TipoFrammento','property' => 'nome','label' => 'Tipo','read_only'=> $this->modifica,'disabled'=> $this->modifica, 'empty_value' => '-'));
		$builder->add('titolo', self::text, array('required' => false, 'label' => 'Titolo'));
		$builder->add('nota', self::textarea, array('required' => false, 'label' => 'Nota', 'attr' => array('placeholder' => 'eventuale nota raw visualizzata sotto il titolo')));
		$builder->add('alias', self::text, array('required' => true, 'label' => 'Alias'));
		$builder->add('callbackPresenza', self::text, array('required' => false, 'label' => 'Callback presenza'));
		$builder->add('submit', self::submit, array('label' => 'Salva'));
    }
	
	
	public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver) {
		parent::configureOptions($resolver);
		$resolver->setDefaults(array(
			'data_class' => 'FascicoloBundle\Entity\Frammento',
			'cascade_validation' => true,
		));
	}
}