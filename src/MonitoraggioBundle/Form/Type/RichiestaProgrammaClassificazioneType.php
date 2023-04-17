<?php

namespace MonitoraggioBundle\Form\Type;

use BaseBundle\Form\CommonType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\ExecutionContextInterface;
use AttuazioneControlloBundle\Entity\RichiestaProgrammaClassificazione;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use MonitoraggioBundle\Repository\TC11TipoClassificazioneRepository;
use MonitoraggioBundle\Repository\TC12ClassificazioneRepository;

class RichiestaProgrammaClassificazioneType extends CommonType implements DataMapperInterface {
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        $data = $builder->getData();    /** @var RichiestaProgrammaClassificazione $data */
        $data = $builder->getInheritData();
        $builder
        ->add('tipo_classificazione', self::entity, [
            'class' => 'MonitoraggioBundle\Entity\TC11TipoClassificazione',
        ])
        ->add('classificazione', self::entity, [
            'class' => 'MonitoraggioBundle\Entity\TC12Classificazione',
        ])
        ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $form = $event->getForm();
            /** @var RichiestaProgrammaClassificazione $data */
            $data = $event->getData();
            $classificazione = is_null($data) ? null : $data->getClassificazione();
            $tipoClassificazione = is_null($classificazione) ? null : $classificazione->getTipoClassificazione();

            $richiestaProgramma = null;
            if (\is_null($data)) {
                $richiestaProgramma = $form->getParent()->getParent()->getData();
            } else {
                $richiestaProgramma = $data->getRichiestaProgramma();
            }

            $form
            ->add('tipo_classificazione', CommonType::entity, [
                'label' => 'Tipo classificazione',
                'class' => 'MonitoraggioBundle\Entity\TC11TipoClassificazione',
                'required' => $options['required'],
                'disabled' => $options['disabled'],
                'placeholder' => false,
                'mapped' => false,
                'attr' => [
                    'onchange' => "onChangeValue(this);",
                    'data-ajax-key' => true,
                ],
                'query_builder' => function (TC11TipoClassificazioneRepository $er) use ($richiestaProgramma) {
                    $qb = $er->tipiConClassificazione();
                    $expr = $qb->expr();
                    return $qb->where(
                        $expr->eq('richieste_programmi', ':richiesta_programma')
                    )
                    ->setParameter('richiesta_programma', $richiestaProgramma);
                },
            ])
            ->add('classificazione', CommonType::entity, [
                'label' => 'Classificazione',
                'class' => 'MonitoraggioBundle\Entity\TC12Classificazione',
                'required' => $options['required'],
                'disabled' => $options['disabled'],
                'attr' => [
                    'data-ajax-class' => 'TC12Classificazione',
                    'data-ajax-value' => true,
                ],
                'query_builder' => function (TC12ClassificazioneRepository $er) use ($tipoClassificazione, $richiestaProgramma) {
                    $qb = $er->querySearchValidClassification();
                    $expr = $qb->expr();

                    return $qb->andWhere(
                        $expr->eq('richiesta_programma', ':richiesta_programma'),
                        $expr->eq('tipo', ':tipo')
                    )
                    ->setParameter('tipo', $tipoClassificazione)
                    ->setParameter('richiesta_programma', $richiestaProgramma);
                },
            ]);
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($options) {
            $form = $event->getForm();
            $data = $event->getData();

            $form->add('classificazione', CommonType::entity, [
                'label' => 'Classificazione',
                'class' => 'MonitoraggioBundle\Entity\TC12Classificazione',
                'required' => $options['required'],
                'disabled' => $options['disabled'],
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\NotNull(),
                ],
                'query_builder' => function (TC12ClassificazioneRepository $er) use ($data) {
                    $tipoClassificazione = is_null($data) ? null : $data['tipo_classificazione'];
                    return $er->createQueryBuilder('u')
                        ->join('u.tipo_classificazione', 'tipo_classificazione')
                        ->where('tipo_classificazione = :tipo')
                        ->setParameter('tipo', $tipoClassificazione);
                },
            ]);
        });

        $builder->setDataMapper($this);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $em = $this->em;
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class' => 'AttuazioneControlloBundle\Entity\RichiestaProgrammaClassificazione',
            'empty_data' => function (FormInterface $form) {
                $richiestaProgramma = $form->getParent()->getData()->getOwner();
                return new RichiestaProgrammaClassificazione($richiestaProgramma);
            },
            'constraints' => [
                /* @var EntityManagerInterface $em */
                new Callback(
                    function (RichiestaProgrammaClassificazione $richiestaProgrammaClassificazione, ExecutionContextInterface $context) use ($em) {
                        /** @var QueryBuilder $qb */
                        $qb = $em->getRepository('MonitoraggioBundle:TC12Classificazione')
                            ->querySearchValidClassification();
                        $expr = $qb->expr();
                        $qb
                            ->select('1')
                            ->andWhere(
                                $expr->eq('classificazione', ':classificazione'),
                                $expr->eq('richiesta_programma', ':richiesta_programma')
                        );

                        $richiestaProgramma = $richiestaProgrammaClassificazione->getRichiestaProgramma();
                        $classificazione = $richiestaProgrammaClassificazione->getClassificazione();

                        $risultato = $qb
                        ->setParameter('classificazione', $classificazione)
                        ->setParameter('richiesta_programma', $richiestaProgramma)
                        ->getQuery()
                        ->setMaxResults(1)
                        ->getOneOrNullResult();

                        $nonValido = false == $risultato;
                        if ($nonValido) {
                            $context
                        ->buildViolation('Classificazione non valida')
                        ->atPath('classificazione')
                        ->addViolation();
                        }
                    }),
            ],
        ]);
    }

    /**
     * @param $data \RichiesteBundle\Entity\Richiesta
     * @param mixed $form
     */
    public function mapDataToForms($data, $form) {
        $classificazione = is_null($data) ? null : $data->getClassificazione();

        $form->rewind();
        while ($form->valid()) {
            switch ($form->key()) {
                case 'tipo_classificazione':
                    $tipoClassificazione = is_null($classificazione) ? null :
                        $classificazione->getTipoClassificazione();
                    $form->current()->setData($tipoClassificazione);
                    break;
                case 'classificazione':
                    $form->current()->setData($classificazione);
                    break;
            }
            $form->next();
        }
    }

    /**
     * @param data \SfingeBundle\Entity\Procedura
     * @param mixed $form
     * @param mixed $data
     */
    public function mapFormsToData($form, &$data) {
        $form->rewind();
        while ($form->valid()) {
            switch ($form->key()) {
                case 'classificazione':
                    $data->setClassificazione($form->current()->getData());
                    break;
            }
            $form->next();
        }
    }
}
