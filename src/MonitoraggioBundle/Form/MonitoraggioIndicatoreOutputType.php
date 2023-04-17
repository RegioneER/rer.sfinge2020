<?php

namespace MonitoraggioBundle\Form;

use BaseBundle\Form\CommonType;
use Doctrine\ORM\EntityRepository;
use MonitoraggioBundle\Entity\TC44_45IndicatoriOutput;
use RichiesteBundle\Entity\IndicatoreOutput;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MonitoraggioIndicatoreOutputType extends CommonType implements DataMapperInterface {
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        CommonType::buildForm($builder, $options);
        $data = $builder->getData();

        $builder->add('indicatore', self::entity, [
            'class' => TC44_45IndicatoriOutput::class,
            'label' => 'Indicatore',
        ])
            ->add('val_programmato', self::moneta, [
                'label' => 'Valore programmato',
            ])
            ->add('valore_monitoraggio', self::moneta, [
                'label' => 'Valore realizzato',
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
                ])
                ->add('val_programmato', self::moneta, [
                    'label' => 'Valore programmato',
                ])
                ->add('valore_monitoraggio', self::moneta, [
                    'label' => 'Valore realizzato',
                ]);
            })

            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($options) {
                $data = $event->getData();
                if (!\array_key_exists('indicatore', $data)) {
                    return;
                }
                $event->getForm()->add('indicatore', CommonType::entity, [
                    'class' => TC44_45IndicatoriOutput::class,
                    'label' => 'Indicatore',
                    'query_builder' => function (EntityRepository $er) use ($data) {
                        return $er->createQueryBuilder('e')
                        ->where('e.id = :id')
                        ->setParameter('id', $data['indicatore']);
                    },
                ])
                ->add('val_programmato', self::moneta, [
                    'label' => 'Valore programmato',
                ])
                ->add('valore_monitoraggio', self::moneta, [
                    'label' => 'Valore realizzato',
                ]);
            })
            ->setDataMapper($this);
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

    /**
     * @param IndicatoreOutput $viewData
     * @param mixed $forms
     */
    public function mapDataToForms($viewData, $forms) {
        // there is no data yet, so nothing to prepopulate
        if (!$viewData instanceof IndicatoreOutput) {
            return;
        }

        /** @var FormInterface[] $forms */
        $forms = iterator_to_array($forms);

        // initialize form field values
        if (\array_key_exists('indicatore', $forms)) {
            $forms['indicatore']->setData($viewData->getIndicatore());
        }
        if (\array_key_exists('val_programmato', $forms)) {
            $forms['val_programmato']->setData($viewData->getValProgrammato());
        }
        $valoreRealizzato = $viewData->getValoreMonitoraggio() ?: $viewData->getValoreValidato() ?: $viewData->getValoreRealizzato();
        if (\array_key_exists('valore_monitoraggio', $forms)) {
            $forms['valore_monitoraggio']->setData($valoreRealizzato);
        }
    }

    /**
     * @param IndicatoreOutput $viewData
     * @param mixed $forms
     * @param mixed $formIterator
     */
    public function mapFormsToData($formIterator, &$viewData) {
        /** @var FormInterface[] $form */
        $form = iterator_to_array($formIterator);

        $viewData->setIndicatore($form['indicatore']->getData());
        $viewData->setValProgrammato($form['val_programmato']->getData());
        $viewData->setValoreMonitoraggio($form['valore_monitoraggio']->getData());
    }
}
