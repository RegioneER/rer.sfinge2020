<?php

namespace IstruttorieBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use IstruttorieBundle\Entity\IstruttoriaRichiesta;
use Doctrine\ORM\EntityRepository;
use CipeBundle\Entity\Classificazioni\CupNatura;
use CipeBundle\Entity\Classificazioni\CupTipologia;
use CipeBundle\Entity\Classificazioni\CupSottosettore;
use CipeBundle\Entity\Classificazioni\CupCategoria;
use CipeBundle\Entity\Classificazioni\CupTipoCoperturaFinanziaria;
use Symfony\Component\Validator\Constraints\Length;
use CipeBundle\Entity\Classificazioni\CupSettore;
use SfingeBundle\Entity\Utente;

class DatiCupType extends CommonType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $data = $builder->getData();/* @var IstruttoriaRichiesta $data */

        $disabled = $this->isDisabled($data, $options['user']);
        $CUPDisabled = $this->isCUPDisabled($data, $options['user']);

        $opzioniForm = \array_merge($options, ['disabled' => $disabled]);

        $this->createForm($data, $builder, $opzioniForm);

        $builder->add('codice_cup', self::text, [
            'label' => 'CUP',
            'constraints' => new Length(['min' => 15, 'max' => 15]),
            'required' => false,
            'disabled' => $CUPDisabled,
        ]);

        $builder->add('pulsanti', self::salva_indietro, [
            "url" => $options["url_indietro"],
            'disabled' => $CUPDisabled,
        ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($opzioniForm) {
            $form = $event->getForm();
            $data = $form->getData();/* @var IstruttoriaRichiesta $data */

            $this->createForm($data, $form, $opzioniForm);
        });
    }

    protected function createForm(IstruttoriaRichiesta $data, $builder, array $options): void {
        $cup_natura = $options["selezioni"]["cup_natura"];
        $scelta_singola = 1 == \count($cup_natura);
        if ($scelta_singola) {
            $data->setCupNatura($cup_natura[0]);
        }
        $esisteCup = !\is_null($data->getCodiceCup());
        $readOnly = $scelta_singola || $esisteCup;
        $builder->add(
            'cup_natura', self::entity, [
                'disabled' => $options['disabled'],
                'class' => CupNatura::class,
                'choices' => $cup_natura,
                'required' => true,
                'placeholder' => $scelta_singola ? false : "-",
                'attr' => ['readonly' => $readOnly],
                'label' => 'Natura',
            ]
        );

        $cup_tipologia = $options["selezioni"]["cup_tipologia"];
        $scelta_singola = 1 == \count($cup_tipologia);
        if ($scelta_singola) {
            $data->setCupTipologia($cup_tipologia[0]);
        }

        $builder->add(
            'cup_tipologia', self::entity, [
                'class' => CupTipologia::class,
                'query_builder' => function (EntityRepository $er) use ($data, $cup_tipologia) {
                    return $er->createQueryBuilder('u')
                        ->where('u = coalesce(:cup_tipologia, u) or u in (:scelte)')
                        ->setParameter('cup_tipologia', $data->getCupTipologia())
                        ->setParameter('scelte', $cup_tipologia);
                },
                'required' => true,
                'disabled' => $options['disabled'],
                'placeholder' => $scelta_singola ? false : "-",
                'attr' => ['readonly' => $readOnly],
                'label' => 'Tipologia',
            ]
        );

        $cup_settore = $options["selezioni"]["cup_settore"];
        $scelta_singola = 1 == \count($cup_settore);
        if ($scelta_singola) {
            $data->setCupSettore($cup_settore[0]);
        }

        $builder->add(
            'cup_settore', self::entity, [
                'class' => CupSettore::class,
                'required' => $options["required_all"],
                'placeholder' => $scelta_singola && $options["required_all"] ? false : "-",
                'attr' => ['readonly' => $readOnly],
                'label' => 'Settore',
                'disabled' => $options['disabled'],
                'query_builder' => function (EntityRepository $er) use ($data, $cup_settore) {
                    return $er->createQueryBuilder('u')
                    ->where('u = coalesce(:cup_settore, u) or u in (:scelte)')
                    ->setParameter('cup_settore', $data->getCupSettore())
                    ->setParameter('scelte', $cup_settore);
                },
            ]
        );

        $cup_sottosettore = $options["selezioni"]["cup_sottosettore"];
        $scelta_singola = 1 == \count($cup_sottosettore);
        if ($scelta_singola) {
            $data->setCupSottosettore($cup_sottosettore[0]);
        }
        $builder->add(
                'cup_sottosettore', self::entity, [
                    'class' => CupSottosettore::class,
                    'disabled' => $options['disabled'],
                    'required' => $options["required_all"],
                    'placeholder' => ($scelta_singola && $options["required_all"]) ? false : "-",
                    'attr' => ['readonly' => ($readOnly) ? true : false],
                    'label' => 'Sottosettore',
                    'query_builder' => function (EntityRepository $er) use ($data, $cup_sottosettore) {
                        return $er->createQueryBuilder('u')
                            ->where('u = coalesce(:cup_sottosettore, u) or u in (:scelte)')
                            ->setParameter('cup_sottosettore', $data->getCupSottosettore())
                            ->setParameter('scelte', $cup_sottosettore);
                    },
                ]
            );

        $cup_categoria = $options["selezioni"]["cup_categoria"];
        $scelta_singola = 1 == \count($cup_categoria);
        if ($scelta_singola) {
            $data->setCupCategoria($cup_categoria[0]);
        }

        $builder->add(
            'cup_categoria', self::entity, [
                'class' => CupCategoria::class,
                'disabled' => $options['disabled'],
                'required' => $options["required_all"],
                'placeholder' => ($scelta_singola && $options["required_all"]) ? false : "-",
                'attr' => ['readonly' => $readOnly],
                'label' => 'Categoria',
                'query_builder' => function (EntityRepository $er) use ($data, $cup_categoria) {
                    return $er->createQueryBuilder('u')
                        ->where('u = coalesce(:cup_categoria, u) or u in (:scelte)')
                        ->setParameter('cup_categoria', $data->getCupCategoria())
                        ->setParameter('scelte', $cup_categoria);
                },
            ]
        );

        if (false == $options['disabled']) {
            $builder->add(
                'cup_tipi_copertura_finanziaria', self::entity, [
                    'class' => CupTipoCoperturaFinanziaria::class,
                    'choices' => $options["selezioni"]["cup_tipi_copertura_finanziaria"],
                    'multiple' => true,
                    'required' => $options["required_all"],
                    'label' => 'Tipi copertura finanziaria',
                    'disabled' => false,
                ]
            );
        }
    }

    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => IstruttoriaRichiesta::class,
            'required_all' => true,
            'validation_groups' => ['Default', 'dati_cup'],
        ]);

        $resolver->setRequired([
            "url_indietro",
            "selezioni",
            "user",
        ]);
    }

    protected function isDisabled(IstruttoriaRichiesta $istruttoria, Utente $user): bool {
        $isProceduraParticolare = $istruttoria->getProcedura()->isProceduraParticolare();
        $isIstruttore = $user->hasRole('ROLE_ISTRUTTORE');
        $esisteCup = !\is_null($istruttoria->getCodiceCup());

        return (! $isIstruttore || $esisteCup) && !$isProceduraParticolare;
    }

    protected function isCUPDisabled(IstruttoriaRichiesta $istruttoria, Utente $user): bool {
        $isSuperAdmin = \is_null($user) ? false : $user->hasRole(Utente::ROLE_SUPER_ADMIN);

        return $this->isDisabled($istruttoria, $user) && !$isSuperAdmin;
    }
}
