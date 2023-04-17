<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MonitoraggioBundle\Form\Type;

use BaseBundle\Form\CommonType;
use \Symfony\Component\OptionsResolver\OptionsResolver;
use \Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Description of AP05StrumentoAttuativoType
 *
 * @author lfontana
 */
class StrumentoAttuativoType extends CommonType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        $data = $builder->getData();
        
        $builder->add('tc15_strumento_attuativo', self::entity, array(
            'disabled' => $options['disabled'],
            'required' => $options['required'],
            'label' => 'Strumento attuativo',
            'class' => 'MonitoraggioBundle\Entity\TC15StrumentoAttuativo',
            'query_builder' => function ($er) use( $data ){
                return $er
                        ->createQueryBuilder('tc15')
                        ->leftJoin('tc15.strumenti_attuativi', 'strumenti')
                        ->leftJoin('strumenti.richiesta', 'richiesta','WITH','richiesta = :richiesta')
                        ->where('richiesta.id is null')
                        ->setParameter('richiesta',$data ? $data->getRichiesta() : NULL);
            }
        ));
        
    }

    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        
        $resolver->setDefaults(array(
            'data_class' => 'AttuazioneControlloBundle\Entity\StrumentoAttuativo',
            'empty_data' => function (FormInterface $form) {
                $richiesta = $richiesta = $form->getParent()->getData()->getOwner();
                return new \AttuazioneControlloBundle\Entity\StrumentoAttuativo($richiesta);
             },
        ));
    }

}
