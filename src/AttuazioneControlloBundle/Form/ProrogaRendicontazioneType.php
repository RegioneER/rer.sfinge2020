<?php

namespace AttuazioneControlloBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormInterface;
use AttuazioneControlloBundle\Entity\ProrogaRendicontazione;
use AttuazioneControlloBundle\Entity\ModalitaPagamento;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use AttuazioneControlloBundle\Entity\AttuazioneControlloRichiesta;
use AttuazioneControlloBundle\Entity\AttuazioneControlloRichiestaRepository;

class ProrogaRendicontazioneType extends CommonType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        /** @var ProrogaRendicontazione|null $data */
        $data = $builder->getData();
        $builder
            ->add('attuazione_controllo_richiesta', self::entity, [
                'class' => AttuazioneControlloRichiesta::class,
            ])
            ->add('modalita_pagamento', self::entity, [
                'class' => ModalitaPagamento::class,
                'label' => 'Modalità di pagamento',
                'required' => true,
            ])
            ->add('data_inizio', self::birthday, [
                'required' => false,
                'label' => 'Data inizio rendicontazione',
                'widget' => 'single_text',
                'input' => 'datetime',
                'format' => 'dd/MM/yyyy',
            ])
            ->add('data_scadenza', self::birthday, [
                'required' => false,
                'label' => 'Data scadenza rendicontazione',
                'widget' => 'single_text',
                'input' => 'datetime',
                'format' => 'dd/MM/yyyy',
            ])

            ->add('submit', self::salva_indietro, [
                'url' => $options['url_indietro'],
            ]);
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $data = $event->getData();
            $event->getForm()->add('attuazione_controllo_richiesta', self::entity, [
                'label' => 'Protocollo progetto',
                'class' => AttuazioneControlloRichiesta::class,
                'required' => true,
                'query_builder' => function (AttuazioneControlloRichiestaRepository $repo) use ($data) {
                    $atc = \is_null($data) ? null : $data->getAttuazioneControlloRichiesta();
                    return $repo->createQueryBuilder('atc')
                                ->where('atc = :atc')
                                ->setParameter('atc', $atc);
                },
                'choice_label' => function (AttuazioneControlloRichiesta $atc) {
                    return $atc->getRichiesta()->getProtocollo();
                },
                'disabled' => !$options['nuova'] || $options['disabled'],
            ]);
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($options) {
            $data = $event->getData();
            $event->getForm()->add('attuazione_controllo_richiesta', self::entity, [
                'class' => AttuazioneControlloRichiesta::class,
                'required' => true,
                'query_builder' => function (AttuazioneControlloRichiestaRepository $repo) use ($data) {
                    $id_atc = $data['attuazione_controllo_richiesta'] ?? null;
                    return $repo->createQueryBuilder('atc')
                                ->where('atc.id = :atc')
                                ->setParameter('atc', $id_atc);
                },
                'disabled' => !$options['nuova'] || $options['disabled'],
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => ProrogaRendicontazione::class,
            'empty_data' => function (FormInterface $form) {
                /** @var AttuazioneControlloRichiesta $atc */
                $atc = $form->get('attuazione_controllo_richiesta')->getData();

                return new ProrogaRendicontazione($atc);
            },
            'nuova' => false,
        ])
        ->setRequired('url_indietro');
    }
}
