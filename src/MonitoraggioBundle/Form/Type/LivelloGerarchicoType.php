<?php

namespace MonitoraggioBundle\Form\Type;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityManager;
use MonitoraggioBundle\Repository\TC36LivelloGerarchicoRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\DataMapperInterface;
use MonitoraggioBundle\Form\Entity\LivelloGerarchico;

class LivelloGerarchicoType extends CommonType implements DataMapperInterface {
    public $em;

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('tabelleStruttura', self::choice, [
            'label' => 'Struttura protocollo',
            'choices_as_values' => true,
            'choices' => \array_flip(LivelloGerarchico::$LIVELLI),
            'expanded' => false,
            'multiple' => true,
            'attr' => [
                'data-ajax-class' => 'TC36LivelloGerarchico',
                'data-ajax-key' => true, ],
        ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $tabelle = $form->get('tabelleStruttura')->getData();

            $form->add('tc36LivelloGerarchico', \BaseBundle\Form\CommonType::entity, [
                'label' => 'Livello Gerarchico',
                'mapped' => false,
                'placeholder' => '-',
                'attr' => [
                    'data-ajax-class' => 'TC36LivelloGerarchico',
                    'data-ajax-value' => true, ],
                'class' => 'MonitoraggioBundle\Entity\TC36LivelloGerarchico',
                'query_builder' => function (TC36LivelloGerarchicoRepository $er) use ($tabelle) {
                    return $er->queryStrutturaProtocollo($tabelle);
                },
            ]);
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();

            $tabelle = $form->get('tabelleStruttura')->getData();

            $form->add('tc36LivelloGerarchico', \BaseBundle\Form\CommonType::entity, [
                'label' => 'Livello Gerarchico',
                'mapped' => false,
                'placeholder' => '-',
                'attr' => [
                    'data-ajax-class' => 'TC36LivelloGerarchico',
                    'data-ajax-value' => 1, ],
                'class' => 'MonitoraggioBundle\Entity\TC36LivelloGerarchico',
                'query_builder' => function (TC36LivelloGerarchicoRepository $er) use ($tabelle) {
                    return $er->queryStrutturaProtocollo($tabelle);
                },
            ]);
        });

        $builder->setDataMapper($this);
    }

    /**
     * @param FormInterface[] $forms a list of {@link FormInterface} instances
     * @param mixed           $data  structured data
     */
    public function mapFormsToData($forms, &$data) {
        $array = iterator_to_array($forms);
        $data = $array['tc36LivelloGerarchico']->getData();
    }

    /**
     * @param mixed           $data  structured data
     * @param FormInterface[] $forms a list of {@link FormInterface} instances
     */
    public function mapDataToForms($data, $forms) {
        if (is_null($data)) {
            return;
        }
        $forms = iterator_to_array($forms);
        if (array_key_exists('tabelleStruttura', $forms)) {
            $forms['tabelleStruttura']->setData($data ? explode(';', $data->getCodStrutturaProt()) : null);
        }
        $forms['tc36LivelloGerarchico']->setData($data);
    }

    public function __construct(EntityManager $em = null) {
        $this->em = $em;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $em = &$this->em;
        $resolver->setDefaults([
            'data_class' => 'MonitoraggioBundle\Entity\TC36LivelloGerarchico',
        ]);
    }
}
