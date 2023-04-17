<?php

namespace MonitoraggioBundle\Form\Ricerca;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use MonitoraggioBundle\Entity\MonitoraggioEsportazioneLogFase;

class RicercaEsportazioneType extends CommonType {

    public function buildForm(FormBuilderInterface $builder, array $options) {

        parent::buildForm($builder, $options);

        $builder->add('num_invio', self::integer, array(
            'required' => false,
            'label' => 'N° Invio'
        ));

        $builder->add('stato', self::choice, array(
            'choices' => array_flip(MonitoraggioEsportazioneLogFase::$FASI),
            'choices_as_values' => true,
            'placeholder' => '-',
            'required' => false,
            'label' => 'Stato esportazione'
        ));
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'MonitoraggioBundle\Form\Entity\RicercaEsportazione'
        ));
    }

}
