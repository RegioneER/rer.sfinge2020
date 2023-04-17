<?php

namespace BaseBundle\Form;

use BaseBundle\Entity\Indirizzo;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityManager;
use GeoBundle\Entity\GeoComune;
use GeoBundle\Entity\GeoComuneRepository;
use GeoBundle\Entity\GeoProvinciaRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class IndirizzoType extends CommonType {
    protected $entityManager;

    public function __construct(EntityManager $objectManager) {
        $this->entityManager = $objectManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $read_only = $options["readonly"];
        $disabled = $options["readonly"];

        $attr = [];
        if (true == $read_only) {
            $attr = ['readonly' => 'readonly'];
        } 

        $required = in_array("persona", $options["validation_groups"]);

        $builder->add('via', self::text, ['label' => 'Indirizzo', 'disabled' => $disabled, 'required' => $required, 'attr' => $attr]);
        $builder->add('numero_civico', self::text, ['label' => 'Numero civico',  'disabled' => $disabled, 'required' => $required, 'attr' => $attr]);

        $builder->add('stato', self::entity, [
            'class' => 'GeoBundle\Entity\GeoStato',
            'choice_label' => 'denominazione',
            'placeholder' => '-',
            'required' => $required,
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
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($disabled,$attr) {
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
                    $provincia = \is_null($soggetto) ? null : $soggetto->getProvincia();

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

        $builder->add('provinciaEstera', self::text,
                ['required' => false, 'label' => 'Provincia / Regione (estera)',  'disabled' => $disabled, 'attr' => $attr]);

        $builder->add('comuneEstero', self::text,
                ['required' => false, 'label' => 'Città (estera)',  'disabled' => $disabled, 'attr' => $attr]);

        $builder->add('cap', self::text,
                ['required' => $required, 'max_length' => 5, 'label' => 'CAP',  'disabled' => $disabled, 'attr' => $attr]);

        $builder->add('localita', self::text,
                ['required' => false, 'label' => 'Località/Frazione',  'disabled' => $disabled, 'attr' => $attr]);

        $builder->add('disabilitaCombo', self::hidden, ['data' => $read_only]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => 'BaseBundle\Entity\Indirizzo',
            'readonly' => false,
            "validation_groups" => ['Default', 'persona'],
            'dataIndirizzo' => null,
        ]);
        $resolver->setRequired("readonly");
    }
}
