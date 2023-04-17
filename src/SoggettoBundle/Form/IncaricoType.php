<?php

namespace SoggettoBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IncaricoType extends CommonType {

    public function buildForm(FormBuilderInterface $builder, array $options) {

        parent::buildForm($builder, $options);

        if ($options["admin"] == false) {
            $builder->add('tipo_incarico', self::entity, array(
                'class' => 'SoggettoBundle:TipoIncarico',
                'choice_label' => 'descrizione',
            ));
        } else {
            $builder->add('tipo_incarico', self::entity, array(
                'class' => 'SoggettoBundle:TipoIncarico',
                'choice_label' => 'descrizione',
                'query_builder' => function(\Doctrine\ORM\EntityRepository $e) {
                    return $e->createQueryBuilder('e')
                                    ->where("e.codice <> 'DELEGATO'");
                }
            ));
        }
        $builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"], "label_salva" => "Avanti"));
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'SoggettoBundle\Entity\IncaricoPersona',
            'admin' => false
        ));
        $resolver->setRequired("url_indietro");
    }

}
