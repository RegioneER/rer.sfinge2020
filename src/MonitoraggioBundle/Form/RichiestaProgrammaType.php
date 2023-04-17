<?php

namespace MonitoraggioBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use AttuazioneControlloBundle\Entity\RichiestaProgramma;
use AttuazioneControlloBundle\Entity\RichiestaProgrammaClassificazione;
use MonitoraggioBundle\Entity\TC11TipoClassificazione;
use Symfony\Component\Validator\ExecutionContextInterface;
use Symfony\Component\Validator\Constraints\Callback;
use AttuazioneControlloBundle\Entity\RichiestaLivelloGerarchico;
use Symfony\Component\Validator\Constraints\Valid;

class RichiestaProgrammaType extends CommonType {
    public function buildForm(\Symfony\Component\Form\FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        $disabled = $options['disabled'];
        $required = $options['required'];

        /** @var RichiestaProgramma $richiestaProgramma */
        $richiestaProgramma = $builder->getData();

        $builder->add('classificazioni', self::collection, array(
            'entry_type' => 'MonitoraggioBundle\Form\Type\RichiestaProgrammaClassificazioneType',
            'label' => false,
            'required' => false,
            'disabled' => $options['ruolo_lettura'],
            'allow_add' => !$disabled,
            'allow_delete' => !$disabled,
            'delete_empty' => true,
            'by_reference' => true,
            'prototype_data' => new RichiestaProgrammaClassificazione($richiestaProgramma),
        ));

        $builder->add('mon_livelli_gerarchici', self::collection, array(
            'entry_type' => 'MonitoraggioBundle\Form\Type\CostoAmmessoType',
            'label' => false,
            'required' => false,
            'disabled' => $options['ruolo_lettura'],
            'delete_empty' => true,
            'prototype_data' => new RichiestaLivelloGerarchico($richiestaProgramma),
            'allow_add' => !$disabled,
            'allow_delete' => !$disabled,
            'entry_options' => array(
                'modifica_importo' => $options['modifica_importo_costo_ammesso'],
            ),
        ));

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();
            $form->add('tc4_programma', \BaseBundle\Form\CommonType::entity_hidden, array(
                    'class' => 'MonitoraggioBundle\Entity\TC4Programma',
                    'attr' => array(
                        'data-ajax-class' => 'TC4Programma',
                        'data-ajax-key' => true,
                    ),
                ));
        });


        $builder->add('submit', self::salva_indietro, array(
            'url' => $options['url_indietro'],
            'disabled' => $options['ruolo_lettura'],
        ));

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
             'classificazioni' => true,
             'modifica_importo_costo_ammesso' => false,
             'data_class' => 'AttuazioneControlloBundle\Entity\RichiestaProgramma',
             'constraints' => array(
                 new Valid(),
                 new Callback(function (RichiestaProgramma $data, ExecutionContextInterface $context) {
                     $classificazioni = $data->getClassificazioni();
                     $hasLineaAzione = \array_reduce($classificazioni->toArray(),
                            function ($carry, RichiestaProgrammaClassificazione $elemento) {
                                $tipoClassificazione = $elemento->getClassificazione()->getTipoClassificazione()->getTipoClass();
                                return $carry || TC11TipoClassificazione::LINEA_AZIONE == $tipoClassificazione;
                            }, false);
                     if (!$hasLineaAzione) {
                         $context
                        ->buildViolation('Classificazione Linea-Azione non presente')
                        ->atPath('classificazioni')
                        ->addViolation();
                     }
                 }),
             ),
         ));
        $resolver->setRequired('ruolo_lettura');
        $resolver->setRequired('url_indietro');
    }
}
