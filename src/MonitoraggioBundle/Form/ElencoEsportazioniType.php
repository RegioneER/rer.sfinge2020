<?php

namespace MonitoraggioBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ElencoEsportazioniType extends CommonType {

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('submit', self::submit, array(
            'label' => 'Crea nuovo invio',
            'disabled' => $options['ctrl_disabled_esportazioni_in_corso']
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        $resolver->setDefault('label', false);
        $resolver->setRequired(array('ctrl_disabled_esportazioni_in_corso',));
    }

}
