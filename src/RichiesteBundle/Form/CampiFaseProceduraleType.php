<?php

namespace RichiesteBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class CampiFaseProceduraleType extends CommonType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('data_avvio_prevista', self::birthday, [
            "label" => $options['labels']['data_avvio_prevista'],
            'widget' => 'single_text',
            'input' => 'datetime',
            'required' => false,
            'format' => 'dd/MM/yyyy', ]);

        $builder->add('data_conclusione_prevista', self::birthday, [
            "label" => $options['labels']['data_conclusione_prevista'],
            'widget' => 'single_text',
            'input' => 'datetime',
            'required' => false,
            'format' => 'dd/MM/yyyy', ]);

        $builder->add('data_avvio_effettivo', self::birthday, [
            "label" => $options['labels']['data_avvio_effettivo'],
            'widget' => 'single_text',
            'input' => 'datetime',
            'required' => false,
            'format' => 'dd/MM/yyyy', ]);

        $builder->add('data_conclusione_effettiva', self::birthday, [
            "label" => $options['labels']['data_conclusione_effettiva'],
            'widget' => 'single_text',
            'input' => 'datetime',
            'required' => false,
            'format' => 'dd/MM/yyyy', ]);

        if ($options['data_approvazione']) {
            $builder->add('data_approvazione', self::birthday, [
                "label" => $options['labels']['data_approvazione'],
                'widget' => 'single_text',
                'input' => 'datetime',
                'required' => false,
                'format' => 'dd/MM/yyyy', ]);
        }

        if (true == $options['attiva_opzionale']) {
            $builder->add('data_opzionale', self::birthday, [
                "label" => $options['labels']['data_opzionale'],
                'widget' => 'single_text',
                'input' => 'datetime',
                'format' => 'dd/MM/yyyy', ]);
        }
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => 'RichiesteBundle\Entity\VoceFaseProcedurale',
            'constraints' => [new Valid()],
            'data_approvazione' => true,
        ]);
        $resolver->setRequired("attiva_opzionale");
        $resolver->setRequired("labels");
    }
}
