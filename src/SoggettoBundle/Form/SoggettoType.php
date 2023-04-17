<?php

namespace SoggettoBundle\Form;

use BaseBundle\Form\CommonType;
use BaseBundle\Validator\Constraints\CfSoggettoConstraint;
use Doctrine\ORM\EntityManager;
use GeoBundle\Entity\GeoComune;
use GeoBundle\Entity\GeoComuneRepository;
use GeoBundle\Entity\GeoProvinciaRepository;
use SoggettoBundle\Entity\FormaGiuridica;
use SoggettoBundle\Entity\FormaGiuridicaRepository;
use SoggettoBundle\Entity\Soggetto;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function is_null;
use function is_object;

class SoggettoType extends CommonType {

    protected $piva_required;
    protected $data_costituzione_required;

    public function __construct() {
        $this->piva_required = false;
        $this->data_costituzione_required = false;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $read_only = $options["readonly"];
        $disabled = $options["readonly"];

        /** @var EntityManager $em */
        $em = $options["em"];

        if (true == $read_only) {
            $attr = ['readonly' => 'readonly'];
        } else {
            $attr = [];
        }

        $builder->add('denominazione', self::text, [
            'required' => true,
            'disabled' => $disabled,
            'label' => 'Denominazione',
            'attr' => $attr,
        ]);

        $builder->add('partita_iva', self::text, [
            'required' => false,
            'disabled' => $disabled,
            'label' => 'Partita iva',
            'attr' => $attr,
            'constraints' => [
                new CfSoggettoConstraint([
                    'obbligatorio' => false,
                    'legaleRappresentante' => null,
                        ]),
            ],
        ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event)use($options, $disabled, $attr) {

            /** @var Soggetto $soggetto */
            $soggetto = $event->getData();
            $form = $event->getForm();

            if (!is_null($soggetto->getId())) {
                $tipoByFormaGiuridica = $soggetto->getTipoByFormaGiuridica();
                $formaGiuridica = $soggetto->getFormaGiuridica()->getCodice();
            } else {
                $tipoByFormaGiuridica = $options['tipo'];
                if ($soggetto->getFormaGiuridica() instanceof FormaGiuridica) {
                    $formaGiuridica = $soggetto->getFormaGiuridica()->getCodice();
                } else {
                    $formaGiuridica = null;
                }
            }

            $form->add('forma_giuridica', self::entity, [
                'class' => 'SoggettoBundle\Entity\FormaGiuridica',
                'placeholder' => '-',
                'disabled' => $disabled,
                'label' => 'Forma giuridica',
                'attr' => $attr,
                'required' => true,
                'query_builder' => function (FormaGiuridicaRepository $repo) use ($tipoByFormaGiuridica, $formaGiuridica) {
                    return $repo->dammiRepoTipoByFormaGiuridicaQb($tipoByFormaGiuridica, $formaGiuridica);
                },
                    ]
            );
        });

        $builder->add('data_costituzione', self::birthday, [
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
            'required' => $this->data_costituzione_required,
            'disabled' => $disabled,
            'label' => 'Data di costituzione',
            'attr' => $attr,]);

        $builder->add('dimensione_impresa', self::entity, [
            'class' => 'SoggettoBundle\Entity\DimensioneImpresa',
            'placeholder' => '-',
            'required' => false,
            'label' => 'Dimensione',
            'disabled' => $disabled,
            'attr' => $attr,
                ]
        );

        $builder->add('codice_ateco', self::entity, ['class' => 'SoggettoBundle\Entity\Ateco',
            'choice_label' => function ($ateco) {
                return $ateco->getCodice() . ' - ' . substr($ateco->getDescrizione(), 0, 89);
            },
            'placeholder' => '-',
            'required' => false,
            'label' => 'Codice Ateco',
            'disabled' => $disabled,
            'attr' => $attr,
        ]);

        $builder->add('codice_ateco_secondario', self::entity, ['class' => 'SoggettoBundle\Entity\Ateco',
            'choice_label' => function ($ateco) {
                return $ateco->getCodice() . ' - ' . substr($ateco->getDescrizione(), 0, 89);
            },
            'placeholder' => '-',
            'required' => false,
            'label' => 'Codice Ateco secondario',
            'disabled' => $disabled,
            'attr' => $attr,
        ]);

        $builder->add('email', self::text, [
            'required' => true,
            'disabled' => $disabled,
            'label' => 'Email',
            'attr' => $attr,
        ]);

        $builder->add('email_pec', self::text, [
            'required' => true,
            'disabled' => $disabled,
            'label' => 'Email PEC',
            'attr' => $attr,
        ]);

        $builder->add('tel', self::text, [
            'required' => true,
            'disabled' => $disabled,
            'label' => 'Telefono',
            'attr' => $attr,
        ]);

        $builder->add('fax', self::text, [
            'required' => false,
            'disabled' => $disabled,
            'label' => 'Fax',
            'attr' => $attr,
        ]);

        $builder->add('stato', self::entity, [
            'class' => 'GeoBundle\Entity\GeoStato',
            'choice_label' => 'denominazione',
            'placeholder' => '-',
            'required' => true,
            'label' => 'Stato',
            'disabled' => $disabled,
            'attr' => $attr,
                ]
        );

        $builder->add('provincia', self::entity, [
            'class' => 'GeoBundle\Entity\GeoProvincia',
            'choice_label' => 'denominazione',
            'placeholder' => '-',
            'required' => true,
            'label' => 'Provincia',
            'disabled' => $disabled,
            'attr' => $attr,
            'query_builder' => function (GeoProvinciaRepository $repo) {
                return $repo->provinceListQb(null);
            },
        ]);

        $builder->add('comune', self::entity, [
            'class' => GeoComune::class,
        ]);
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($disabled, $attr) {
            /** @var Soggetto $soggetto */
            $soggetto = $event->getData();
            $form = $event->getForm();
            $form->add('comune', self::entity, [
                'class' => GeoComune::class,
                'choice_label' => 'denominazione',
                'placeholder' => '-',
                'required' => true,
                'label' => 'Comune',
                'disabled' => $disabled,
                'attr' => $attr,
                'query_builder' => function (GeoComuneRepository $repo) use ($soggetto) {
                    $provincia = is_null($soggetto) ? null : $soggetto->getProvincia();

                    return $repo->comuniListQb($provincia);
                },
            ]);
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($disabled) {
            /** @var array $data */
            $data = $event->getData();
            $form = $event->getForm();
            $form->add('comune', self::entity, [
                'disabled' => $disabled,
                'class' => GeoComune::class,
                'query_builder' => function (GeoComuneRepository $repo) use ($data) {
                    $qb = $repo->createQueryBuilder('comune')
                            ->join('comune.provincia', 'provincia')
                            ->where(
                                    'provincia.id = :id_provincia'
                            )
                            ->setParameter('id_provincia', $data['provincia'] ?? null);

                    return $qb;
                },
            ]);
        });

        $builder->add('provinciaEstera', self::text, [
            'required' => false,
            'label' => 'Provincia / Regione (estera)',
            'disabled' => $disabled,
            'attr' => $attr,
        ]);

        $builder->add('comuneEstero', self::text, [
            'required' => true,
            'label' => 'Città (estera)',
            'disabled' => $disabled,
            'attr' => $attr,
        ]);

        $builder->add('via', self::text, [
            'required' => true,
            'disabled' => $disabled,
            'label' => 'Via',
            'attr' => $attr,
        ]);

        $builder->add('civico', self::text, [
            'required' => true,
            'disabled' => $disabled,
            'label' => 'Numero civico',
            'attr' => $attr,]);

        $builder->add('cap', self::text, [
            'required' => true,
            'disabled' => $disabled,
            'label' => 'Cap',
            'attr' => $attr,
        ]);

        $builder->add('localita', self::text, [
            'required' => false,
            'disabled' => $disabled,
            'label' => 'Località',
            'attr' => $attr,]);

        $builder->add('disabilitaCombo', self::hidden, [
            'data' => $read_only,
        ]);

        $builder->add('pulsanti', self::salva_indietro, ["url" => $options["url_indietro"], 'disabled' => $disabled]);

        $builder->add('senza_piva', self::checkbox, [
            'disabled' => $disabled,
            "label" => "Soggetto Giuridico senza Partita IVA (Attenzione: il mancato possesso della Partita IVA potrebbe pregiudicare la partecipazione ad alcuni bandi)",
            "required" => false,
        ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($disabled, $attr) {
            $form = $event->getForm();

            /** @var Soggetto $data */
            $data = $event->getData();

            $form->add('codice_fiscale', self::text, [
                'required' => true,
                'disabled' => $disabled || (null !== $data->getCodiceFiscale()),
                'label' => 'Codice fiscale',
                'attr' => $attr,
            ]);

            $form->add('laboratorio_ricerca', self::checkbox, [
                "label" => "Laboratorio di ricerca\Università",
                "required" => false,
                "disabled" => (null !== $data->getLaboratorioRicerca()),
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => 'SoggettoBundle\Entity\Soggetto',
            'readonly' => false,
            'validation_groups' => function ($form) {
                $data = $form->getData();
                if (!is_object($data->getStato())) {
                    return ["Default"];
                }
                if ('Italia' == $data->getStato()->getDenominazione()) {
                    return ["Default", "statoItalia"];
                }

                return ["Default"];
            },
            "dataIndirizzo" => null,
            'em' => null,
        ]);

        $resolver->setRequired("url_indietro");
        $resolver->setRequired("tipo");
    }

}
