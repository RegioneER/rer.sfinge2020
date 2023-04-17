<?php
namespace RichiesteBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class FirmatarioType extends CommonType
{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $firmatari = $options['delegati'];
        $builder->add('',self::entity, array(
            'class' => 'AnagraficheBundle\Entity\Persona',
            'choices' => $firmatari,
            'label' => 'Firmatario',
            'required' => true,
            'constraints' => array(
                new NotNull(),
            )
        ))
        ->add('submit', self::salva_indietro,array(
            'url' => false,
        ));
    }
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'RichiesteBundle\Entity\Richiesta',
        ))
        ->setRequired('delegati');
    }
}
