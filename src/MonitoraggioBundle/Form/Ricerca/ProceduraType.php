<?php

namespace MonitoraggioBundle\Form\Ricerca;

use BaseBundle\Form\CommonType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
/**
* @author lfontana
*/
class ProceduraType extends CommonType{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        
        $builder->add('asse', self::entity, array(
            'label' => 'Asse',
            'class' => 'SfingeBundle\Entity\Asse',
            'required' => false,
        ));
        
        $builder->add('tipo', self::choice, array(
            'label' => 'Tipo',
            'required' => false,
            'choices_as_values' => true,
            'choices' => array(
                'Bando' => 'BANDO',
                "Manifestazione d'interesse" => 'MANIFESTAZIONE_INTERESSE',
                'Assistenza tecnica' => 'ASSISTENZA_TECNICA',
                'ingegneria finanziaria' => 'INGEGNERIA_FINANZIARIA',
            ),
            'placeholder' => '-',
        ));
        
        $builder->add('numeroProceduraAttivazione', self::entity, array(
            'label' => 'Atto',
            'required' => false,
            'class' => 'SfingeBundle\Entity\Atto'
        ));
        
        $builder->add('titolo', self::text, array(
            'label' => 'Titolo',
            'required' => false,
        ));
        
        $builder->add('porFesr', self::choice, array(
            'label' => 'POR FESR',
            'required' => false,
            'choices_as_values' => true,
            'choices' => array(
                'No' => 0,
                'Sì' => 1,
            ),
        ));
        
        $builder->add('datiCompleti', self::choice, array(
            'label' => 'Dati completi',
            'required' => false,
            'choices_as_values' => true,
            'choices' => array(
                'No' => 0,
                'Sì' => 1,
            ),
        ));
    }

    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
            'data_class' => 'MonitoraggioBundle\Form\Entity\RicercaProcedura',
        ));
    }
}