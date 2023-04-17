<?php

namespace MonitoraggioBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class TrasferimentoType extends CommonType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('bando', self::entity, array(
            'label' => 'Procedura attivazione',
            'disabled' => $options['disabled'],
            'required' => true,
            'class' => 'SfingeBundle\Entity\Procedura',
        ));

        $builder->add('causale_trasferimento', self::entity, array(
            'label' => 'Causale trasferimento',
            'disabled' => $options['disabled'],
            'required' => true,
            'class' => 'MonitoraggioBundle\Entity\TC49CausaleTrasferimento',
        ));

        $builder->add('soggetto', self::entity, array(
            'label' => 'Destinatario',
            'disabled' => $options['disabled'],
            'required' => true,
            'class' => 'SoggettoBundle\Entity\Soggetto',
            'query_builder' => function (EntityRepository $er) use ($builder) {
                $soggetto = is_null($builder->getData()) ? null : $builder->getData()->getSoggetto();

                return $er->createQueryBuilder('s')->where('s = :soggetto')->setParameter('soggetto', $soggetto);
            },
        ));

        $builder->add('cod_trasferimento', self::text, array(
            'label' => 'Codice trasferimento',
            'disabled' => $options['disabled'],
            'required' => true,
        ));

        $builder->add('data_trasferimento', self::birthday, array(
            'label' => 'Data trasferimento',
            'disabled' => $options['disabled'],
            'required' => true,
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
        ));

        $builder->add('importo_trasferimento', self::moneta, array(
            'label' => 'Importo trasferimento',
            'disabled' => $options['disabled'],
            'required' => true,
        ));

        $builder->add('programma', self::entity, array(
            'label' => 'Programma',
            'disabled' => $options['disabled'],
            'required' => true,
            'class' => 'MonitoraggioBundle\Entity\TC4Programma',
        ));

        $builder->add('submit', self::salva_indietro, array(
            'url' => $options['url_indietro'],
            'disabled' => false,
        ));

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($options) {
            $form = $event->getForm();
            $data = $event->getData();

            $form->add('soggetto', \BaseBundle\Form\CommonType::entity, array(
                'label' => 'Destinatario',
                'disabled' => $options['disabled'],
                'required' => true,
                'class' => 'SoggettoBundle\Entity\Soggetto',
                'query_builder' => function (EntityRepository $er) use ($data) {
                    $soggetto = is_null($data) ? null : $data['soggetto'];

                    return $er->createQueryBuilder('s')->where('s = :soggetto')->setParameter('soggetto', $soggetto);
                },
            ));
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('data_class', 'MonitoraggioBundle\Entity\Trasferimento');
        $resolver->setRequired('url_indietro');
        $resolver->setDefault('disabled', false);
    }
}
