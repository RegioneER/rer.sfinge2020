<?php

namespace MonitoraggioBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use MonitoraggioBundle\Entity\TC44_45IndicatoriOutput;
use RichiesteBundle\Entity\IndicatoreOutput;

class IndicatoreOutputType extends CommonType {
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        CommonType::buildForm($builder, $options);
        $data = $builder->getData();

        $builder->add('indicatore', self::entity, [
            'class' => TC44_45IndicatoriOutput::class,
            'disabled' => $options['disabled'] || $options['to_beneficiario'],
            'label' => 'Indicatore',
            'required' => !$options['disabled'] && !$options['to_beneficiario'],
        ])
            ->add('val_programmato', self::moneta, [
            'label' => 'Valore programmato',
            'required' => !$options['disabled'] && $options['to_richiesta'],
            'disabled' => $options['disabled'] || $options['to_beneficiario'],
        ])
            ->add('valore_realizzato', self::moneta, [
            'label' => 'Valore realizzato',
            'required' => $options['to_beneficiario'],
            'disabled' => $options['disabled'] || $options['to_richiesta'],
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
                /** @var IndicatoreOutput $data */
                $data = $event->getData();
                $form = $event->getForm();
                if (\is_null($data)) {
                    return;
                }
                $form->add('indicatore', CommonType::entity, [
                    'class' => TC44_45IndicatoriOutput::class,
                    'label' => 'Indicatore',
                    'disabled' => $options['disabled'] || $options['to_beneficiario'],
                    'query_builder' => function (EntityRepository $er) use ($data) {
                        return $er->createQueryBuilder('e')
                            ->join('MonitoraggioBundle:IndicatoriOutputAzioni indicatori', 'with e = indicatori.indicatoreOutput')
                            ->join('indicatori.azione', 'azioni')
                            ->join('azioni.procedure', 'procedura')
                            ->join('procedura.richieste', 'richieste')
                            ->join('procedura.asse', 'asse')
                            ->where(
                                'e = coalesce(:indicatore,e)',
                                'richieste.id = :richiesta_id',
                                'procedura.asse = indicatori.asse'
                            )
                        ->setParameter('richiesta_id', $data->getRichiesta()->getId())
                        ->setParameter('indicatore', $data->getIndicatore());
                    },
                ]);
            })

            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($options) {
                $data = $event->getData();
                if (!\array_key_exists('indicatore', $data)) {
                    return;
                }
                $event->getForm()->add('indicatore', CommonType::entity, [
                    'class' => TC44_45IndicatoriOutput::class,
                    'disabled' => $options['disabled'] || $options['to_beneficiario'],
                    'label' => 'Indicatore',
                    'query_builder' => function (EntityRepository $er) use ($data) {
                        return $er->createQueryBuilder('e')
                        ->where('e.id = :id')
                        ->setParameter('id', $data['indicatore']);
                    },
                ]);
            });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        CommonType::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class' => IndicatoreOutput::class,
            'empty_data' => function (FormInterface $form) {
                $richiesta = $form->getParent()->getParent()->getData();

                return new IndicatoreOutput($richiesta);
            },
            'to_beneficiario' => false,
            'to_richiesta' => false,
        ]);
    }
}
