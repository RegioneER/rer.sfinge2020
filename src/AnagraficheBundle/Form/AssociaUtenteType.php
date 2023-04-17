<?php

namespace AnagraficheBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AnagraficheBundle\Entity\Persona;
use AnagraficheBundle\Entity\PersonaRepository;
use SfingeBundle\Entity\Utente;
use SfingeBundle\Entity\UtenteRepository;

class AssociaUtenteType extends CommonType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('persona', self::entity, [
            'class' => Persona::class,
            'placeholder' => '-',
            'required' => true,
            'label' => 'Persona',
            'query_builder' => function (PersonaRepository $repo) use ($options) {
                $q = $repo->createQueryBuilder('p')
                          ->leftJoin('p.utente', 'u')
                          ->where('u.id IS NULL');

                if ($options['is_pa']) {
                    return $q->andWhere(
                            'p.codice_fiscale IS NULL'
                        );
                }

                return $q->andWhere(
                        'p.codice_fiscale IS NOT NULL'
                    );
            },
        ]
        );

        $builder->add('utente', self::entity, [
            'class' => Utente::class,
            'placeholder' => '-',
            'required' => true,
            'label' => 'Utente',
            'query_builder' => function (UtenteRepository $repo) use ($options) {
                $q = $repo->createQueryBuilder('u')
                    ->where('u.persona IS NULL');
                if ($options['is_pa']) {
                    return $q->andWhere(
                        "u.roles like '%ROLE_UTENTE_PA%' OR u.roles like '%ROLE_MANAGER_PA%' OR u.roles like '%ROLE_ADMIN_PA%'"
                    );
                }
                return $q->andWhere(
                    "u.roles like '%ROLE_UTENTE%'",
                    "u.roles not like '%ROLE_UTENTE_PA%'"
                );
            },
        ]
        );

        $builder->add('pulsanti', self::salva, [
            'label' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'is_pa' => false,
        ]);
    }
}
