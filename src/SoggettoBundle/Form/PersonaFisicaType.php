<?php
namespace SoggettoBundle\Form;

use GeoBundle\Entity\GeoComune;
use GeoBundle\Entity\GeoComuneRepository;
use GeoBundle\Entity\GeoProvinciaRepository;
use SoggettoBundle\Entity\Soggetto;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class PersonaFisicaType extends SoggettoType
{
    protected $piva_required;
    protected $data_costituzione_required;

    public function __construct()
    {
        $this->piva_required = false;
        $this->data_costituzione_required = false;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $read_only = $options["readonly"];
        $disabled = $options["readonly"];

        $attr = [];
        if ($read_only == true) {
            $attr = ['readonly' => 'readonly'];
        }

        $builder->add('denominazione', self::text, [
            'required' => false,
            'disabled' => $disabled,
            'label' => 'Denominazione',
            'attr' => $attr,
        ]);

        $builder->add('codice_fiscale', self::text, [
            'required' => false,
            'disabled' => $disabled,
            'label' => 'Codice fiscale',
            'attr' => $attr]);

        $builder->add('email', self::text, [
            'required' => true,
            'disabled' => $disabled,
            'label' => 'Email (standard, non pec)',
            'attr' => $attr,
        ]);

        $builder->add('email_pec', self::text, [
            'required' => false,
            'disabled' => $disabled,
            'label' => 'Email PEC',
            'attr' => $attr,
            //'constraints' => [new NotBlank()],
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
            'required' => false,
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

        $builder->add('pulsanti',
            self::salva_indietro, [
                "url" => $options["url_indietro"], 'disabled' => $disabled
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'SoggettoBundle\Entity\Soggetto',
            'validation_groups' => ['persona_fisica', ],
        ]);
        $resolver->setRequired("url_indietro");
        $resolver->setRequired("tipo");
        $resolver->setRequired("readonly");
    }
}
