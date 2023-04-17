<?php

namespace AttuazioneControlloBundle\Form;

use AttuazioneControlloBundle\Form\Entity\ChecklistSpecifica;
use BaseBundle\Form\CommonType;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChecklistSpecificaType extends CommonType
{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('procedure', self::entity,[
            'class' => Procedura::class,
            'multiple' => true,
            'expanded' => false,
            'choice_label' => function(Procedura $procedura){
                return $procedura->getId() . ' - ' . $procedura->getTitolo();
            }
        ]);        
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => ChecklistSpecifica::class,
        ]);
    }
}