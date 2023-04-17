<?php

namespace AnagraficheBundle\Form;

use BaseBundle\Form\CommonType;
use BaseBundle\Validator\Constraints\CfConstraint;
use GeoBundle\Entity\GeoComune;
use GeoBundle\Entity\GeoComuneRepository;
use GeoBundle\Entity\GeoProvinciaRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonaType extends CommonType {
    protected $entityManager;

    public function __construct(\Doctrine\ORM\EntityManager $objectManager) {
        $this->entityManager = $objectManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $read_only = $options["readonly"];
        $disabled = $options["readonly"];
        $disabilitaEmail = $options["disabilitaEmail"];
        $disabilitaCf = $options["disabilitaCf"];

        $attr = [];
        if (true == $read_only) {
            $attr = ['readonly' => 'readonly'];
        }

        $attr_email = [];
        if (true == $disabilitaEmail) {
            $attr_email = ['readonly' => 'readonly'];
        }

        $attr_cf = [];
        if (true == $disabilitaCf) {
            $attr_cf = ['readonly' => 'readonly'];
        }

        $required = in_array("persona", $options["validation_groups"]);

        $builder->add('nome', self::text, [
            'required' => true,
            'disabled' => $disabled,
            'label' => 'Nome',
            'attr' => $attr,
        ]);
        $builder->add('cognome', self::text, [
            'required' => true,
            'disabled' => $disabled,
            'label' => 'Cognome',
            'attr' => $attr,
        ]);
        $builder->add('sesso', self::choice, [
            'choices' => ['M' => 'M', 'F' => 'F'],
            'choices_as_values' => true,
            'required' => true,
            'expanded' => false,
            'multiple' => false,
            'label' => 'Sesso',
            'disabled' => $disabled,
            'attr' => $attr,
        ]);

        $builder->add('nazionalita', self::entity, [
            'class' => 'GeoBundle\Entity\GeoStato',
            'choice_label' => 'denominazione',
            'label' => 'Nazionalità',
            'disabled' => $disabled,
        ]
        );

        $builder->add('data_nascita', self::birthday, [
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
            'required' => $required,
            'disabled' => $disabled,
            'label' => 'Data di nascita',
            'attr' => $attr, ]);

        $builder->add('stato_nascita', self::entity, [
            'class' => 'GeoBundle\Entity\GeoStato',
            'choice_label' => 'denominazione',
            'placeholder' => '-',
            'label' => 'Stato di nascita',
            'disabled' => $disabled,
            'required' => $required,
            'attr' => $attr, ]);

        $builder->add('provincia', self::entity, [
            'class' => 'GeoBundle\Entity\GeoProvincia',
            'choice_label' => 'denominazione',
            'placeholder' => '-',
            'required' => true,
            'label' => 'Provincia di nascita',
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
                'label' => 'Comune di nascita',
                'disabled' => $disabled,
                'attr' => $attr,
                'query_builder' => function (GeoComuneRepository $repo) use ($soggetto) {
                    $provincia = \is_null($soggetto) ? null : $soggetto->getProvincia();

                    return $repo->comuniListQb($provincia, 1);
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

        $builder->add('disabilitaCombo', self::hidden, ['data' => $read_only]);

        $builder->add('codice_fiscale', self::text, [
            'constraints' => [new CfConstraint([])],
            'required' => $required,
            'label' => 'Codice fiscale',
            'disabled' => $disabled,
            'attr' => $attr_cf, ]
        );

        $builder->add('luogo_residenza', 'BaseBundle\Form\IndirizzoType', [
            'readonly' => $read_only,
			'validation_groups' => $options["validation_groups"],
			'label' => false,
        ]);

        $builder->add('telefono_principale', self::text, [
            'required' => true,
            'disabled' => $disabled,
            'label' => 'Telefono',
            'attr' => $attr,
        ]);
        $builder->add('fax_principale', self::text, [
            'required' => false,
            'disabled' => $disabled,
            'label' => 'Fax',
            'attr' => $attr,
        ]);
        $builder->add('email_principale', self::text, [
            'required' => true,
            'disabled' => $disabilitaEmail,
            'label' => 'Email (standard, non pec)',
            'attr' => $attr_email,
        ]);
        $builder->add('telefono_secondario', self::text, [
            'required' => false,
            'disabled' => $disabled,
            'label' => 'Telefono',
            'attr' => $attr,
        ]);
        $builder->add('fax_secondario', self::text, [
            'required' => false,
            'disabled' => $disabled,
            'label' => 'Fax',
            'attr' => $attr,
        ]);
        $builder->add('email_secondario', self::text, [
            'required' => false,
            'disabled' => $disabled,
            'label' => 'Email (standard, non pec)',
            'attr' => $attr,
        ]);

        $builder->add('salva_invia', self::salva_indietro, [
            'mostra_indietro' => $options['mostra_indietro'],
            'url' => $options["url_indietro"],
            'disabled' => $disabled,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => 'AnagraficheBundle\Entity\Persona',
            'readonly' => false,
            'disabilitaEmail' => true,
            'disabilitaCf' => false,
            "mostra_indietro" => true,
			"validation_groups" => ['Default', 'persona'],
			'dataIndirizzo' => null,
			'dataPersona' => null,
        ]);

        $resolver->setRequired("url_indietro");
    }
}
