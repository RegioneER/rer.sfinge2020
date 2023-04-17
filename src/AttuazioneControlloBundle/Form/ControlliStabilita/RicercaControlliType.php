<?php

namespace AttuazioneControlloBundle\Form\ControlliStabilita;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use SfingeBundle\Entity\Atto;

class RicercaControlliType extends CommonType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        
        $utente = $builder->getData()->getUtente();
        if ($utente->isInvitalia()) {
            $builder->add('procedura', self::entity, array(
                'class' => 'SfingeBundle\Entity\Procedura',
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'label' => 'Procedura',
                'query_builder' => function(\SfingeBundle\Entity\ProceduraRepository $rep) {
                    $qb = $rep->createQueryBuilder('procedura')
                            ->where('procedura.id = 95');
                    
                    return $qb;
                } 
            ));
        }elseif($utente->isOperatoreCogea()) {
            $builder->add('procedura', self::entity, array(
                'class' => 'SfingeBundle\Entity\Procedura',
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'label' => 'Procedura',
                'query_builder' => function(\SfingeBundle\Entity\ProceduraRepository $rep) {
                    $qb = $rep->createQueryBuilder('procedura')
                            ->where('procedura.id IN (2,5,58,64,67,70,72,75,77,81,83,107,110,111,112,116,128,140,142,161) ');
                    
                    return $qb;
                } 
            ));
        } else {
            $builder->add('procedura', self::entity, array(
                'class' => 'SfingeBundle\Entity\Procedura',
                'expanded' => false,
                'multiple' => false,
                'required' => false,
                'label' => 'Procedura'
            ));
        }

        $builder->add('atto', self::entity, [
            'class' => Atto::class,
            'placeholder' => '-',
            'required' => false,
            'label' => 'Atto',
            'query_builder' => function (\SfingeBundle\Entity\AttoRepository $er) {
                return $er->createQueryBuilder('atto')
                                ->join('SfingeBundle:Procedura', 'procedura', 'with', 'atto = procedura.atto');
            },
            'choice_label' => 'numero',
        ]);

        $builder->add('protocollo', self::text, ['required' => false, 'label' => 'Protocollo']);

        $builder->add('completata', self::choice, [
            'required' => false,
            'label' => 'Stato controllo',
            'placeholder' => '-',
            'choices_as_values' => true,
            'choices' => [
                'Completata' => true,
                'Non completata' => false,
            ],
        ]);

        $builder->add('denominazione', self::text, ['required' => false, 'label' => 'Denominazione soggetto']);
        $builder->add('codice_fiscale', self::text, ['required' => false, 'label' => 'Codice fiscale soggetto']);
        
        $builder->add('comune', self::entity, [
            'class' => 'GeoBundle\Entity\GeoComune',
            'expanded' => false,
            'multiple' => false,
            'required' => false,
            'label' => 'Comune',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => 'AttuazioneControlloBundle\Form\Entity\ControlliStabilita\RicercaControlli',
        ]);
    }
}
