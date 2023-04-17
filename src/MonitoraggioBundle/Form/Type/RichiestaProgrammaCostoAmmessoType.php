<?php

namespace MonitoraggioBundle\Form\Type;

use BaseBundle\Form\CommonType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AttuazioneControlloBundle\Entity\RichiestaProgramma;
use AttuazioneControlloBundle\Entity\RichiestaLivelloGerarchico;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class RichiestaProgrammaCostoAmmessoType extends CommonType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        $disabled = $options['disabled'];
        $required = $options['required'];

        /** @var RichiestaProgramma $richiestaProgramma */
        $richiestaProgramma = $builder->getData();

        $builder->add('mon_livelli_gerarchici', self::collection, [
            'entry_type' => 'MonitoraggioBundle\Form\Type\CostoAmmessoType',
            'label' => false,
            'required' => false,
            'prototype_data' => new RichiestaLivelloGerarchico($richiestaProgramma),
            'allow_add' => !$disabled,
            'allow_delete' => !$disabled,
            'delete_empty' => true,
            'entry_options' => [
                'modifica_importo' => $options['modifica_importo_costo_ammesso'],
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
             'classificazioni' => true,
             'modifica_importo_costo_ammesso' => false,
             'data_class' => 'AttuazioneControlloBundle\Entity\RichiestaProgramma',
             'constraints' => [
                 new Valid(),
             ],
         ]);
    }

    public function finishView(FormView $view, FormInterface $form, array $options) {
        $view->children['mon_livelli_gerarchici']->children = 
            \array_filter($view->children['mon_livelli_gerarchici']->children, function(FormView $f){
            /** @var RichiestaLivelloGerarchico $livello */
            $livello = $f->vars['value'];
            $codLivelloAsse = $livello
                ->getRichiestaProgramma()
                ->getRichiesta()
                ->getProcedura()
                ->getAsse()
                ->getLivelloGerarchico()
                ->getCodLivGerarchico();
            return $codLivelloAsse != $livello->getTc36LivelloGerarchico()->getCodLivGerarchico();
        });
    }
}
