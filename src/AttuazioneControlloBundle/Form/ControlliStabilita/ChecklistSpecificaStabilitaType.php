<?php

namespace AttuazioneControlloBundle\Form\ControlliStabilita;

use AttuazioneControlloBundle\Form\Entity\ChecklistSpecifica;
use BaseBundle\Form\CommonType;
use AttuazioneControlloBundle\Entity\Controlli\ControlloCampione;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChecklistSpecificaStabilitaType extends CommonType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('procedure', self::entity, [
            'class' => ControlloCampione::class,
            'query_builder' => function(\Doctrine\ORM\EntityRepository $er) {
                return $er->createQueryBuilder('c')
                                ->where('c.tipo_controllo = :tipo')
                                ->setParameter('tipo', 'STABILITA');
            },
            'multiple' => true,
            'expanded' => false,
            'choice_label' => function(ControlloCampione $procedura) {
                return $procedura->getId() . ' - ' . $procedura->getDescrizione();
            }
        ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => ChecklistSpecifica::class,
        ]);
    }

}
