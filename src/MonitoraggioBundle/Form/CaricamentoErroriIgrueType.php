<?php

namespace MonitoraggioBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\NotNull;

class CaricamentoErroriIgrueType extends CommonType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add('documento_from_igrue', 'DocumentoBundle\Form\Type\DocumentoFileType', array(
            'label' => false,
            'tipo' => 'FROM_IGRUE',
            'constraints' => array(
                new \Symfony\Component\Validator\Constraints\Valid(),
                new NotNull(),
            ),
        ))
        ->add('submit', self::salva_indietro, array(
            'label' => false,
            'label_salva' => 'Importa',
            'url' => $options['url_indietro'],
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
            'data_class' => 'MonitoraggioBundle\Entity\MonitoraggioEsportazione',
        ))
        ->setRequired(array('tipologia_documento', 'url_indietro'));
    }
}
