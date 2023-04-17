<?php
/**
 * @author lfontana
 */

namespace MonitoraggioBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use RichiesteBundle\Entity\IndicatoreOutput;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use MonitoraggioBundle\Form\IndicatoreOutputType;
use RichiesteBundle\Entity\Richiesta;

class RichiestaIndicatoreOutputType extends CommonType {
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $data = $builder->getData();
        $builder->add('mon_indicatore_output', CollectionType::class, [
            'entry_type' => IndicatoreOutputType::class,
            'allow_add' => true && !$options['to_beneficiario'],
            'allow_delete' => true && !$options['to_beneficiario'],
            'delete_empty' => true,
            'by_reference' => true,
            'prototype_data' => new IndicatoreOutput($data),
            'entry_options' => [
                'to_richiesta' => $options['to_richiesta'],
                'to_beneficiario' => $options['to_beneficiario'],
                'validation_groups' => $options['to_beneficiario'] ? ['rendicontazione_beneficiario'] : $options['validation_groups'],
            ],
            'constraints' => [
                new Valid(),
            ],
        ])
        ->add('submit', self::salva, [
            'label' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class' => Richiesta::class,
            'to_beneficiario' => false,
            'to_richiesta' => false,
        ]);
    }
}
