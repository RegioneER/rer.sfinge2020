<?php

namespace MonitoraggioBundle\Form\Ricerca;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;

/**
* @author lfontana
*/
class RicercaAssociazioneIndicatoriAzioniType extends CommonType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        
        $builder->add('assi', self::entity, [
            'label' => 'Assi',
            'class' => 'SfingeBundle\Entity\Asse',
            'required' => false,
            'multiple' => true,
        ]);
        
        $builder->add('azioni', self::entity, array(
            'label' => 'Azioni',
            'required' => false,
            'class' => 'SfingeBundle\Entity\Azione',
            'multiple' => true,
        ));
        
        $builder->add('indicatori', self::entity, array(
            'label' => 'Indicatori output',
            'required' => false,
            'class' => 'MonitoraggioBundle\Entity\TC44_45IndicatoriOutput',
            'multiple' => true,
        ));
        
    }

    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
            'data_class' => 'MonitoraggioBundle\Form\Entity\RicercaAssociazioneAzioniIndicatori',
        ));
    }
}
