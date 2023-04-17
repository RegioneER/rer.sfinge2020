<?php

namespace MonitoraggioBundle\Form\Ricerca;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use MonitoraggioBundle\Repository\MonitoraggioConfigurazioneEsportazioneTavoleRepository;
use MonitoraggioBundle\Repository\MonitoraggioEsportazioneRepository;


class ErroriEsportazioneType extends CommonType{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('struttura', self::choice,array(
            'label' => 'Struttura',
            'choices_as_values' => true,
            'choices' => MonitoraggioEsportazioneRepository::GetAllStrutture(),
            'required' => true,
            'placeholder' => '-',
        ))
        ->add('progressivo', self::integer, array(
            'label' => 'Progressivo PUC'
        ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'MonitoraggioBundle\Form\Entity\RicercaTavolaEsportata',
        ));
    }
}