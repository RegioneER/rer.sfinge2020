<?php

namespace SfingeBundle\Form;

use AttuazioneControlloBundle\Entity\ModalitaPagamento;
use BaseBundle\Form\CommonType;
use SfingeBundle\Entity\Bando;
use SfingeBundle\Entity\Procedura;
use SfingeBundle\Entity\ProceduraRepository;
use SfingeBundle\Form\Entity\RicercaModalitaPagamentoProcedura;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RicercaModalitaPagamentoProceduraType extends CommonType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        $builder->add('procedura', self::entity, [
            'class' => Procedura::class,
            'choice_label' => function (Bando $bando) {
                return $bando->getId() . ' - ' . $bando->getTitolo();
            },
            'placeholder' => '-',
            'query_builder' => function (ProceduraRepository $repo) {
                return $repo->createQueryBuilder('p')
                ->where('p INSTANCE OF SfingeBundle:Bando OR p INSTANCE OF SfingeBundle:ProceduraPA');
            },
        ]);

        $builder->add('modalita', self::entity, [
            'class' => ModalitaPagamento::class,
            'placeholder' => '-',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => RicercaModalitaPagamentoProcedura::class,
        ]);
    }
}
