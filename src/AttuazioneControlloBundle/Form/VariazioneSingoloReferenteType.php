<?php

namespace AttuazioneControlloBundle\Form;

use AnagraficheBundle\Entity\Persona;
use AnagraficheBundle\Entity\PersonaRepository;
use AttuazioneControlloBundle\Entity\VariazioneSingoloReferente;
use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class VariazioneSingoloReferenteType extends CommonType {
    /** @var array */
    private $parametri;

    public function __construct(array $parametri) {
        $this->parametri = $parametri;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use($options){
            $form = $event->getForm();
            /** @var VariazioneSingoloReferente $variazioneReferente */
            $variazioneReferente = $event->getData();
            $procedura = $variazioneReferente->getProponente()->getRichiesta()->getProcedura();
            $id_procedura = "{$procedura->getId()}";
            $parametriProcedura = $this->parametri[$id_procedura] ?? [];
            $form->add('persona', self::entity, [
                'class' => Persona::class,
                'required' => true,
                'label' => "Referente",
                'choice_label' => function (Persona $persona) {
                    return $persona . ' - ' . $persona->getCodiceFiscale();
                },
                'query_builder' => function (PersonaRepository $repo) use ($variazioneReferente) {
                    return $repo->createQueryBuilder('p')
                                ->where('p = :persona')
                                ->setParameter('persona', $variazioneReferente->getPersona());
                },
                'attr' => [
                    'data-toggle' => 'tooltip',
                    'title' => 'ok',
                ],
            ]);
            if (\in_array('qualifica', $parametriProcedura)) {
                $form->add('qualifica', self::text, [
                    "label" => "Qualifica",
                    "required" => true,
                    "attr" => ["maxlength" => 100],
                    'constraints' => [
                        new NotNull(),
                    ],
                ]);
            }
            if (\in_array('email_pec', $parametriProcedura)) {
                $form->add('email_pec', self::text, [
                    "label" => "Email PEC",
                    "required" => true,
                    "attr" => ["maxlength" => 128],
                    'constraints' => [
                        new NotBlank([
                            'message' => "Specificare l'indirizzo email PEC",
                        ]),
                    ],
                ]);
            }

            if (\in_array('ruolo', $parametriProcedura)) {
                $form->add('ruolo', self::text, [
                    "required" => true,
                    "attr" => ["maxlength" => 100],
                    'constraints' => [
                        new NotNull(),
                    ],
                ]);
            }

            $form->add('submit', self::salva_indietro, [
                'url' => $options['indietro'],
            ]);
        });
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $persona = $event->getData();
            $form = $event->getForm();
            $form->add('persona', self::entity, [
                'class' => Persona::class,
                'required' => true,
                'label' => "Referente",
                'choice_label' => function (Persona $persona) {
                    return $persona . ' - ' . $persona->getCodiceFiscale();
                },
                'query_builder' => function (PersonaRepository $repo) use ($persona) {
                    return $repo->createQueryBuilder('p')
                                ->where('p.id = :persona')
                                ->setParameter('persona', $persona['persona']);
                },
                'attr' => [
                    'data-toggle' => 'tooltip',
                    'title' => 'ok',
                ],
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => VariazioneSingoloReferente::class,
            'validation_groups' => function (FormInterface $form): array {
                /** @var VariazioneSingoloReferente $variazioneReferente */
                $variazioneReferente = $form->getData();
                $procedura = $variazioneReferente->getVariazione()->getRichiesta()->getProcedura();
                return [
                    'Default',
                    "bando_{$procedura->getId()}",
                ];
            },
        ]);

        $resolver->setRequired('indietro');
    }
}
