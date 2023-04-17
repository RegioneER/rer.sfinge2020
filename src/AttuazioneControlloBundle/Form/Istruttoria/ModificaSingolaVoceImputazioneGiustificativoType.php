<?php

namespace AttuazioneControlloBundle\Form\Istruttoria;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AttuazioneControlloBundle\Entity\VocePianoCostoGiustificativo;
use RichiesteBundle\Entity\VocePianoCosto;
use RichiesteBundle\Entity\VocePianoCostoRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use RichiesteBundle\Service\IGestorePianoCosto;
use RichiesteBundle\Service\GestorePianoCostoService;
use RichiesteBundle\Entity\Proponente;
use Symfony\Component\Form\FormInterface;
use AttuazioneControlloBundle\Form\Entity\ModificaVociImputazioneGiustificativo;
use Doctrine\ORM\EntityManagerInterface;

class ModificaSingolaVoceImputazioneGiustificativoType extends CommonType {
    /** @var GestorePianoCostoService */
    protected $service;

    /** @var EntityManagerInterface */
    protected $em;

    public function __construct(GestorePianoCostoService $service, EntityManagerInterface $em) {
        $this->service = $service;
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('voce_piano_costo', self::entity, [
            'class' => VocePianoCosto::class,
        ]);
        $builder->add('importo', self::importo, [
            "currency" => "EUR",
            "scale" => 2,
            "grouping" => true,
        ])
        ->add('annualita', self::choice, [
            'choices' => [],
        ]);
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            /** @var VocePianoCostoGiustificativo $vocePianoCostoGiustificativo */
            $vocePianoCostoGiustificativo = $event->getData();
            if (\is_null($vocePianoCostoGiustificativo)) {
                return;
            }
            $giustificativo = $vocePianoCostoGiustificativo->getGiustificativoPagamento();
            $form->add('voce_piano_costo', self::entity, [
                'class' => VocePianoCosto::class,
                'choice_label' => function (VocePianoCosto $vocePianoCosto) {
                    $pianoCosto = $vocePianoCosto->getPianoCosto();
                    return $pianoCosto->getTitolo() . ' (' . $pianoCosto->getSezionePianoCosto()->getTitoloSezione() . ')';
                },
                'query_builder' => function (VocePianoCostoRepository $repo) use ($giustificativo) {
                    return $repo->createQueryBuilder('voce')
                                ->innerJoin('voce.piano_costo', 'piano_costo')
                                ->innerJoin('piano_costo.tipo_voce_spesa', 'tipo_voce_spesa')
                                ->innerJoin('voce.proponente', 'proponente')
                                ->innerJoin('proponente.richiesta', 'richiesta')
                                ->innerJoin('richiesta.attuazione_controllo', 'atc')
                                ->innerJoin('atc.pagamenti', 'pagamenti')
                                ->innerJoin('pagamenti.giustificativi', 'giustificativi')
                                ->leftJoin('giustificativi.proponente', 'g_prop')
                                ->where(
                                    "tipo_voce_spesa.codice <> 'TOTALE'",
                                    'COALESCE(piano_costo.voce_spesa_generale, 0) = 0',
                                    'giustificativi = :giustificativo',
                                    'proponente = COALESCE(g_prop, proponente)'
                                )

                                ->setParameter('giustificativo', $giustificativo);
                },
            ]);
            $mandatario = $giustificativo->getPagamento()->getRichiesta()->getMandatario();
            /** @var VocePianoCosto $vocePianoCosto */
            $vocePianoCosto = $vocePianoCostoGiustificativo->getVocePianoCosto();
            /** @var Proponente $proponente */
            $proponente = $vocePianoCosto ? $vocePianoCosto->getProponente() : $mandatario;
            $procedura = $giustificativo->getProcedura();
            /** @var IGestorePianoCosto $gestorePiano */
            $gestorePiano = $this->service->getGestore($procedura);
            /** @var array $annualita */
            $annualita = $gestorePiano->getAnnualitaRendicontazione($proponente) ?: $gestorePiano->getAnnualita($proponente);
            $form->add('annualita', self::choice, [
                'choices_as_values' => \true,
                'choices' => \array_flip($annualita),
            ]);
            
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();

            /** @var ModificaVociImputazioneGiustificativo $parentData */
            $parentData = $form->getParent()->getParent()->getData();
            $giustificativo = $parentData->getGiustificativo();

            $form->add('voce_piano_costo', self::entity, [
                'class' => VocePianoCosto::class,
                'query_builder' => function (VocePianoCostoRepository $repo) use ($giustificativo) {
                    return $repo->createQueryBuilder('voce')
                                ->innerJoin('voce.piano_costo', 'piano_costo')
                                ->innerJoin('piano_costo.tipo_voce_spesa', 'tipo_voce_spesa')
                                ->innerJoin('voce.proponente', 'proponente')
                                ->innerJoin('proponente.richiesta', 'richiesta')
                                ->innerJoin('richiesta.attuazione_controllo', 'atc')
                                ->innerJoin('atc.pagamenti', 'pagamenti')
                                ->innerJoin('pagamenti.giustificativi', 'giustificativi')
                                ->leftJoin('giustificativi.proponente', 'g_prop')
                                ->where(
                                    "tipo_voce_spesa.codice <> 'TOTALE'",
                                    'COALESCE(piano_costo.voce_spesa_generale, 0) = 0',
                                    'giustificativi = :giustificativo',
                                    'proponente = COALESCE(g_prop, proponente)'
                                )

                                ->setParameter('giustificativo', $giustificativo);
                },
                'choice_label' => function (VocePianoCosto $vocePianoCosto) {
                    $pianoCosto = $vocePianoCosto->getPianoCosto();
                    return $pianoCosto->getTitolo() . ' (' . $pianoCosto->getSezionePianoCosto()->getTitoloSezione() . ')';
                },
            ]);
            /** @var array $data */
            $data = $event->getData();
            /** @var VocePianoCosto $vocePianoCosto */
            $vocePianoCosto = $this->em->getRepository(VocePianoCosto::class)->find($data['voce_piano_costo']);

            $mandatario = $giustificativo->getPagamento()->getRichiesta()->getMandatario();
            /** @var Proponente $proponente */
            $proponente = $vocePianoCosto ? $vocePianoCosto->getProponente() : $mandatario;
            $procedura = $giustificativo->getProcedura();
            /** @var IGestorePianoCosto $gestorePiano */
            $gestorePiano = $this->service->getGestore($procedura);
            /** @var array $annualita */
            $annualita = $gestorePiano->getAnnualitaRendicontazione($proponente) ?: $gestorePiano->getAnnualita($proponente);

            $form->add('annualita', self::choice, [
                'choices_as_values' => \true,
                'choices' => \array_flip($annualita),
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => VocePianoCostoGiustificativo::class,
            'empty_data' => function (FormInterface $form) {
                /** @var ModificaVociImputazioneGiustificativo $parent */
                $parent = $form->getParent()->getParent()->getData();
                $giustificativo = $parent->getGiustificativo();
                $voce = new VocePianoCostoGiustificativo();
                $voce->setGiustificativoPagamento($giustificativo);
                $vocePianoCosto = $form->get('voce_piano_costo')->getData();
                $voce->setVocePianoCosto($vocePianoCosto);

                return $voce;
            },
        ]);
    }
}
