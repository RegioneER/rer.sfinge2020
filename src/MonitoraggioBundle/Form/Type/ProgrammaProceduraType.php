<?php

namespace MonitoraggioBundle\Form\Type;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormInterface;

class ProgrammaProceduraType extends CommonType{
    public function buildForm( FormBuilderInterface $builder, array $options ) {
        parent::buildForm($builder, $options);

        $builder->add('tc4_programma', self::entity,array(
            'class' => 'MonitoraggioBundle\Entity\TC4Programma',
            'label' => 'Programma',
            'required' => false,
            'placeholder' => '-',
            'constraints' => array(
                new \Symfony\Component\Validator\Constraints\NotNull(),
            ),
        ));
        $builder->add('importo', self::moneta, array(
            'required' => true,
            'label' => 'Importo',
            'constraints' => array(
                new \Symfony\Component\Validator\Constraints\NotNull(),
            ),
        ));
    }

    public function configureOptions( OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
            'data_class' => 'SfingeBundle\Entity\ProgrammaProcedura',
            'label' => 'Programma',
            'empty_data' => function ( \Symfony\Component\Form\FormInterface $form) {
                $procedura = $form->getParent()->getData()->getOwner();
                return new \SfingeBundle\Entity\ProgrammaProcedura($procedura);
            }
        ));
        
    }
}