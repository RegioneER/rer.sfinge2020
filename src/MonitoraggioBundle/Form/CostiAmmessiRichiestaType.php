<?php

namespace MonitoraggioBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class CostiAmmessiRichiestaType extends CommonType{
        /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('mon_programmi', self::collection, array(
            'entry_type' => 'MonitoraggioBundle\Form\Type\RichiestaProgrammaCostoAmmessoType',
            'label' => false,
            'required' => false,
            'disabled' => $options['ruolo_lettura'],
            'allow_add' => false,
            'allow_delete' => false,
            'prototype' => false,
            'by_reference' => true,
            'entry_options' => array(
                'classificazioni' => false,
                'modifica_importo_costo_ammesso' => true
            ),
        ));

        if(!$options['ruolo_lettura']) {
            $builder ->add('submit', self::salva, array());
        }


    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'RichiesteBundle\Entity\Richiesta',
        ));
        $resolver->setRequired('ruolo_lettura');
    }
        
}