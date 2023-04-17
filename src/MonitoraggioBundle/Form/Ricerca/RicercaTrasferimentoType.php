<?php

namespace MonitoraggioBundle\Form\Ricerca;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RicercaTrasferimentoType extends CommonType {

    public function buildForm(FormBuilderInterface $builder, array $options) {

        parent::buildForm($builder, $options);

        $builder->add('bando', self::entity, array(
            'class' => 'SfingeBundle\Entity\Procedura',
            'placeholder' => '-',
            'required' => false,
            'label' => 'Titolo Procedura attivazione'
        ));

        $builder->add('causale_trasferimento', self::entity, array(
            'class' => 'MonitoraggioBundle\Entity\TC49CausaleTrasferimento',
            'placeholder' => '-',
            'required' => false,
        ));

        //$builder->add('cod_trasferimento', self::text, array('required' => false, 'label' => 'Codice trasferimento'));

//        $builder->add('data_trasferimento', self::birthday, array(
//            'label' => 'Data trasferimento',
//            'required' => false,
//            "widget" => "single_text",
//            "input" => "datetime",
//            "format" => "dd/MM/yyyy",
//        ));

        $builder->add('soggetto', self::text, array(
            'label' => 'Destinatario',
            'required' => false,
        ));
        
        $builder->add('importo_trasferimento', self::moneta, array('required' => false, 'label' => 'Importo trasferimento'));
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'MonitoraggioBundle\Form\Entity\RicercaTrasferimento'
        ));
    }

}
