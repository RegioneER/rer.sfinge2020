<?php

namespace MonitoraggioBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use RichiesteBundle\Entity\PianoCosto;
use MonitoraggioBundle\Entity\TC37VoceSpesa;

class PianoCostoVoceSpesaType extends CommonType {
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('mon_voce_spesa', self::entity, [
            'class' => TC37VoceSpesa::class,
            'label' => 'Voce spesa',
            'required' => false,
            'placeholder' => '-',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => PianoCosto::class,
        ]);
    }
}
