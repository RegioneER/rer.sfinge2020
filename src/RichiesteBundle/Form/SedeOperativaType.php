<?php

namespace RichiesteBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;


class SedeOperativaType extends CommonType{

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $data = $builder->getData(); /** @var RichiesteBundle\Entity\SedeOperativa $data */
      
        $soggetto = \is_null($data) ? NULL : $data->getProponente()->getSoggetto();
        $builder->add('sede', self::entity, array(
            'label' => 'Sede',
            'required' => false,
            'placeholder' => '-',
            'class' => 'SoggettoBundle\Entity\Sede',
            'query_builder' => function(EntityRepository $er) use($soggetto){
                return $er->createQueryBuilder('sede')
                    ->join('sede.soggetto','soggetto')
                    ->where('soggetto = :soggetto')
                    ->setParameter('soggetto', $soggetto);
            },
            'choice_label' => function($sede){   /** @var \SoggettoBundle\Entity\Sede $sede */
                $estero = !\is_null($sede->getIndirizzo()) && $sede->getIndirizzo()->getStato()->getCodice() == '11101';
                $indirizzo = $sede->getIndirizzo();
                $res = $sede->getDenominazione() .' '. $indirizzo->getVia() . ' ' . $indirizzo->getNumeroCivico() . ' ';
                $res .= $estero ?
                    $indirizzo->getComuneEstero() . ' ' . $indirizzo->getProvinciaEstera():
                    $indirizzo->getComune()->getDenominazione() . ' ' . $indirizzo->getComune()->getProvincia()->getSiglaAutomobilistica();
                return $res;
            }
        ))
        ->addEventListener(FormEvents::PRE_SET_DATA,function(FormEvent $event){
            $form = $event->getForm();
            $data = $event->getData();
            $form->add('sede', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', array(
                'label' => 'Sede',
                'required' => false,
                'placeholder' => '-',
                'class' => 'SoggettoBundle\Entity\Sede',
                'query_builder' => function(EntityRepository $er) use($data){
                    return $er->createQueryBuilder('sede')
                    ->join('sede.soggetto', 'soggetto')
                        ->where('soggetto = :soggetto')
                        ->setParameter('soggetto', $data->getProponente()->getSoggetto());
                },
                'choice_label' => function($sede){   /** @var \SoggettoBundle\Entity\Sede $sede */
                    $estero = !\is_null($sede->getIndirizzo()) && $sede->getIndirizzo()->getStato()->getCodice() == '11101';
                    $indirizzo = $sede->getIndirizzo();
                    $res = $sede->getDenominazione() .' '. $indirizzo->getVia() . ' ' . $indirizzo->getNumeroCivico() . ' ';
                    $res .= $estero ?
                        $indirizzo->getComuneEstero() . ' ' . $indirizzo->getProvinciaEstera():
                        $indirizzo->getComune()->getDenominazione() . ' ' . $indirizzo->getComune()->getProvincia()->getSiglaAutomobilistica();
                    return $res;
                }
            ));
        });
    }
	
	public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
			'data_class' => 'RichiesteBundle\Entity\SedeOperativa',
            'salva_contributo' => false,
        ));
    }
}