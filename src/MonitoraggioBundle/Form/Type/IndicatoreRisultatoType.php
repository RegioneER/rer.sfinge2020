<?php

namespace MonitoraggioBundle\Form\Type;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Doctrine\ORM\EntityRepository;
use RichiesteBundle\Entity\IndicatoreRisultato;
use MonitoraggioBundle\Entity\TC42_43IndicatoriRisultato;
use Symfony\Component\Form\FormInterface;

class IndicatoreRisultatoType extends CommonType {
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            /** @var IndicatoreRisultato|null $indicatore */
            $indicatore = $event->getData();
            $form = $event->getForm();

            $form->add('indicatore', self::entity, [
                'class' => TC42_43IndicatoriRisultato::class,
                'label' => 'Indicatore risultato',
                'query_builder' => function (EntityRepository $er) use ($indicatore) {
                    $qb = $er->createQueryBuilder('indicatore');
                    $expr = $qb->expr();

                    return $qb
                            ->leftJoin('indicatore.mappingObiettivoSpecifico', 'mappingObiettivoSpecifico')
                            ->leftJoin('mappingObiettivoSpecifico.obiettivoSpecifico', 'obiettivo_specifico')
                            ->leftJoin('obiettivo_specifico.azioni', 'azioni')
                            ->leftJoin('azioni.procedure', 'procedura')
                            ->leftJoin('RichiesteBundle:Richiesta', 'richiesta', 'with', $expr->eq('richiesta.procedura', 'procedura'))
                            ->where(
                                $expr->orX(
                                    $expr->eq('richiesta', ':richiesta'),
                                    $expr->eq('indicatore', ':indicatore')
                                )
                            )
                            ->setParameter('richiesta', $indicatore ? $indicatore->getRichiesta() : null)
                            ->setParameter('indicatore', $indicatore ? $indicatore->getIndicatore() : null);
                },
            ]);
        })

        ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            if (!\array_key_exists('indicatore', $data)) {
                return;
            }
            $event->getForm()->add('indicatore', self::entity, [
                'class' => TC42_43IndicatoriRisultato::class,
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
        $resolver->setDefaults([
            'data_class' => IndicatoreRisultato::class,
            'empty_data' => function (FormInterface $form) {
                $richiesta = $form->getParent()->getParent()->getData();

                return new IndicatoreRisultato($richiesta);
            },
        ]);
    }
}
