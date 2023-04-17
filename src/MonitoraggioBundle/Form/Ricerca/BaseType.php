<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Form\Ricerca;

use BaseBundle\Form\CommonType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;


/**
 * Description of BaseType
 *
 * @author lfontana
 */
class BaseType extends CommonType{
    
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        $reflect = new \ReflectionClass($options['data_class']);
        $properties = $reflect->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED);
        $reader =  new \Doctrine\Common\Annotations\AnnotationReader();
        
        $annotations = array_filter(array_map( function($property) use ($reader){
            $res= $reader->getPropertyAnnotation($property, 'MonitoraggioBundle\Annotations\RicercaFormType');
            if( !is_null($res) && is_null( $res->property ) ){
                $res->property = $property->name;
            return $res;
            }
        }, $properties));
        
        usort($annotations, function($property1, $property2){
            if($property1->ordine == $property2->ordine){
                return 0;
            }
            return $property1->ordine < $property2->ordine ? -1 : 1;
        });
        
        foreach( $annotations as $field){
            $builder->add($field->property, strpos($field->type, '\\') ? $field->type : constant( 'self::'.$field->type), array_merge( 
                    array(
                        'required' => false,
                        'label' => $field->label,
                    )
                    ,is_null($field->options) ? array() : $field->options));
        }
    }
    
    public function configureOptions( OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
			'data_class' => function (\Symfony\Component\Form\FormBuilderInterface $form) {
                return  $form->getConfig()->getOption("data_class");
            },
		));
    }
}
