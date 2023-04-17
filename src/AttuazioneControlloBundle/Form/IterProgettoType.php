<?php

namespace AttuazioneControlloBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use MonitoraggioBundle\Entity\TC46FaseProcedurale;
use AttuazioneControlloBundle\Entity\IterProgetto;
use MonitoraggioBundle\Repository\TC46FaseProceduraleRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class IterProgettoType extends CommonType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
		
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            /** @var IterProgetto $iter */
            $iter = $event->getData();
            $form = $event->getForm();
            $richiesta = $iter ? $iter->getRichiesta() : null;

            $form->add('fase_procedurale', self::entity, [
                'class' => TC46FaseProcedurale::class,
                'label' => 'Fase',
                'required' => true,
                'disabled' => $options['to_beneficiario'],
                'query_builder' => function (TC46FaseProceduraleRepository $er) use ($richiesta) {
                    $natura = $richiesta ? $richiesta->getIstruttoria()->getCupNatura() : null;

                    $qb =  $er->createQueryBuilder('fase');
                    $expr = $qb->expr();

                    return $qb
                    ->leftJoin('fase.iter', 'iter')
                    ->leftJoin('iter.richiesta', 'richiesta')
                    ->where(
                        $expr->orX(
                            'fase.codice_natura_cup = coalesce(:codice_natura, fase.codice_natura_cup)',
                            $expr->eq('richiesta', ':richiesta')
                        )
                    )
                    ->setParameter('codice_natura', $natura ? $natura->getCodice() : null)
                    ->setParameter('richiesta', $richiesta);
                },
            ]);

            $form->add('data_inizio_prevista', self::birthday, [
                'required' => true,
                'label' => 'Data inizio prevista',
                'widget' => 'single_text',
                'input' => 'datetime',
                'format' => 'dd/MM/yyyy',
                'constraints' => [
                    new NotBlank(),
                    new Date(),
                ],
                'disabled' => $options['to_beneficiario'],
            ]);

            $form->add('data_inizio_effettiva', self::birthday, [
                'required' => false,
                'label' => 'Data inizio effettiva',
                'widget' => 'single_text',
                'input' => 'datetime',
                'format' => 'dd/MM/yyyy',
                'constraints' => [
                    new Date(),
                ],
            ]);

            $form->add('data_fine_prevista', self::birthday, [
                'required' => true,
                'label' => 'Data fine prevista',
                'widget' => 'single_text',
                'input' => 'datetime',
                'format' => 'dd/MM/yyyy',
                'constraints' => [
                    new NotBlank(),
                    new Date(),
                ],
                'disabled' => $options['to_beneficiario'],
            ]);

            $form->add('data_fine_effettiva', self::birthday, [
                'required' => false,
                'label' => 'Data fine effettiva',
                'widget' => 'single_text',
                'input' => 'datetime',
                'format' => 'dd/MM/yyyy',
                'constraints' => [
                    new Date(),
                ],
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class' => IterProgetto::class,
             'empty_data' => function (FormInterface $form) {
                 $richiesta = $form->getParent()->getParent()->getData();
                 return new IterProgetto($richiesta);
             },
             'to_beneficiario' => false,
        ]);
    }
}
