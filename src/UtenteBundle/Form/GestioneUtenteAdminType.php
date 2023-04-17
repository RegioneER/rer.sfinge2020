<?php

/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 18/01/16
 * Time: 15:58
 */

namespace UtenteBundle\Form;

use BaseBundle\Form\CommonType;
use Doctrine\ORM\EntityRepository;
use SfingeBundle\Entity\PermessoFunzionalita;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class GestioneUtenteAdminType extends CommonType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $constraints = [new NotBlank()];

        $builder->add('username', self::text, [
            'required' => true,
            'label' => 'Username',
            'constraints' => $constraints,
        ]);
        $builder->add('email', self::text, [
            'required' => true,
            'label' => 'Email',
            'constraints' => $constraints,
        ]);
        $builder->add('roles', self::choice, [
            'choices_as_values' => true,
            'choices' => \array_flip($options["ruoli"]),
            'required' => true,
            'constraints' => $constraints,
            "expanded" => false,
            "multiple" => true,
            'label' => 'Ruoli', ]
        );
        $builder->add('permessi_funzionalita', self::entity, [
            'class' => PermessoFunzionalita::class,
            'choice_label' => 'descrizione',
            'label' => 'Permessi',
            'expanded' => true,
            'multiple' => true,
            'required' => false,
            'query_builder' => function(EntityRepository $repo){
                return $repo->createQueryBuilder('p')
                ->where('p.codice IN (:tipi_ammessi)')
                ->setParameter('tipi_ammessi', ['GESTIONE_ASSISTENZA_TECNICA']);
            }
        ]);
        $builder->add('submit', 'submit', ['label' => 'Salva']);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setRequired("ruoli");
    }
}
