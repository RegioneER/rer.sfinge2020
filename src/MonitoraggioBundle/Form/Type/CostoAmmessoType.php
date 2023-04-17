<?php

namespace MonitoraggioBundle\Form\Type;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use AttuazioneControlloBundle\Entity\RichiestaLivelloGerarchico;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use MonitoraggioBundle\Repository\TC36LivelloGerarchicoRepository;
use Symfony\Component\Form\FormInterface;

class CostoAmmessoType extends CommonType {
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('tc36_livello_gerarchico', self::entity, array(
            'class' => 'MonitoraggioBundle\Entity\TC36LivelloGerarchico',
            'label' => 'Livello gerarchico',
        ));

        if($options['modifica_importo']){
            $builder->add('importo_costo_ammesso', self::moneta, array(
                'label' => 'Importo ammesso',
            ));
        }

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use($options){
            $form = $event->getForm();
            /** @var RichiestaLivelloGerarchico|null $data */
            $data = $event->getData();

            /** @var RichiestaProgramma $programma */
            $programma = NULL;
            if(\is_null($data)){
                $programma = $event->getForm()->getParent()->getParent()->getNormData();
            }
            else{
                $programma = $data->getRichiestaProgramma();
            }

            $form->add('tc36_livello_gerarchico', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', array(
                'class' => 'MonitoraggioBundle\Entity\TC36LivelloGerarchico',
                'label' => 'Livello gerarchico',
                'disabled' => $options['disabled'] || $options['modifica_importo'],
                'query_builder' => function (TC36LivelloGerarchicoRepository $er) use ($programma) {
                    if(\is_null($programma)){
                        return ;
                    }
                    return $er->livelliPerRichiestaProgrammaQueryBuilder($programma);
                },
            ));
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'AttuazioneControlloBundle\Entity\RichiestaLivelloGerarchico',
            'modifica_importo' => false,
            'empty_data' => function (FormInterface $form) {
                $richiestaProgramma = $form->getParent()->getData()->getOwner();
                return new RichiestaLivelloGerarchico($richiestaProgramma);
            },            
        ));
    }
}
