<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace SfingeBundle\Form;

use BaseBundle\Form\CommonType;
use MonitoraggioBundle\Repository\TC4ProgrammaRepository;
use SfingeBundle\Entity\Procedura;

/**
 * Description of BandoProgrammaType.
 *
 * @author lfontana
 */
class ProgrammaProceduraType extends CommonType
{
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options)
    {
        $data = $builder->getData();
        $formCompilato = !is_null($data->getTc4Programma());
        /** @var Procedura $procedura */
        $procedura = is_null($data) ? null : $data->getProcedura();

        $builder->add('tc4_programma', self::entity, array(
            'label' => 'Programma',
            'class' => 'MonitoraggioBundle:TC4Programma',
            'disabled' => $formCompilato || $options['disabled'],
            'query_builder' => function (TC4ProgrammaRepository $er) use ($procedura) {
                return $er->CreateQueryBuilder('u')
                    ->leftJoin('u.procedure', 'programmaProcedura')
                    ->leftJoin('programmaProcedura.procedura', 'procedura', 'with', 'procedura = :procedura')
                    ->where('programmaProcedura.id is NULL ',
                    'u.cod_programma = :cci')
                    ->setParameter('procedura', $procedura)
                    ->setParameter('cci', $procedura ? $procedura->getCodiceCci() : null);
            },
        ));

        $builder->add('importo', self::moneta, array(
            'label' => 'Importo',
            'required' => true,
            'disabled' => $options['disabled'],
        ));

        $builder->add('submit', self::salva_indietro, array(
            'label_salva' => $formCompilato ? 'Salva' : 'Aggiungi',
            'required' => false,
            'mostra_indietro' => $formCompilato,
            'url' => $options['url_indietro'],
        ));
    }

    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setRequired(array(
            'url_indietro',
        ));

        $resolver->setDefault('data_class', 'SfingeBundle\Entity\ProgrammaProcedura');
    }
}
