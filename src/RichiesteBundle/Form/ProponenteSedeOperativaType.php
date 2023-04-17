<?php

namespace RichiesteBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use RichiesteBundle\Entity\Proponente;

class ProponenteSedeOperativaType extends CommonType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('sedi', self::collection, [
            'label' => false,
            'entry_type' => SedeOperativaType::class,
            'entry_options' => [
                'label' => false,
                'required' => false,
            ],
            'required' => false,
        ])
        ->add('submit', self::salva_indietro, [
            'url' => $options['url_indietro'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => Proponente::class,
            'validation_groups' => false,
            'constraints' => [
                new Callback([
                    'callback' => function (Proponente $proponente, ExecutionContextInterface $ex) {
                        /** @var \RichiesteBundle\Entity\SedeOperativa|false $sede */
                        $sede = $proponente->getSedi()->first();    
                        if (!$sede || !$sede->getSede()) {
                            $ex->addViolation("E' necessario indicare la sede operativa");
                        }
                    },
                ]),
            ],
        ])
            ->setRequired("url_indietro");
    }
}
