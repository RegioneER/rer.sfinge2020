<?php

namespace MonitoraggioBundle\Form\Type;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityManager;
use MonitoraggioBundle\Repository\TC16LocalizzazioneGeograficaRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use MonitoraggioBundle\Entity\TC16LocalizzazioneGeografica;
use Symfony\Component\Form\DataMapperInterface;

/**
 * Description of LocalizzazioneGeograficaType.
 *
 * @author lfontana
 */
class LocalizzazioneGeograficaType extends CommonType implements DataMapperInterface {
    public $em;

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $valori = $this->em->getRepository('MonitoraggioBundle:TC16LocalizzazioneGeografica')->findAllProvincie();
        $valoriRegione = $this->em->getRepository('MonitoraggioBundle:TC16LocalizzazioneGeografica')->findAllRegioni();
        $builder->add('regione', self::choice, array(
            'choices_as_values' => true,
            'choices' => $valoriRegione,
            'choice_label' => 'descrizione',
            'choice_value' => 'codice',
            'attr' => array(
                'data-ajax-regione' => true,
            ),
            'placeholder' => '-',
            'disabled' => true,
        ));
        $builder->add('provincia', self::choice, array(
            'label' => 'Provincia',
            'choices_as_values' => true,
            'choices' => $valori,
            'choice_label' => 'descrizione',
            'choice_value' => 'codice',
            'group_by' => 'regione',
            'attr' => array(
                'data-ajax-provincia' => true,
                'data-ajax-class' => 'TC16LocalizzazioneGeografica',
                'data-ajax-key' => true, ),
            'placeholder' => '-',
        ));

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $entity = $event->getData();
            $codice = is_null($entity) ? null : $entity->getCodiceProvincia();

            $form->add('comune', \BaseBundle\Form\CommonType::entity, array(
                'label' => null,
                'mapped' => false,
                'placeholder' => '-',
                'choice_label' => 'descrizione_comune',
                'class' => 'MonitoraggioBundle\Entity\TC16LocalizzazioneGeografica',
                'query_builder' => function (TC16LocalizzazioneGeograficaRepository $er) use ($entity) {
                    return $er->createQueryBuilder('u')
                                    ->where('u.codice_provincia = :codProvincia')
                                    ->setParameter('codProvincia', is_null($entity) ? null : $entity->getCodiceProvincia());
                },
                'attr' => array(
                    'data-ajax-class' => 'TC16LocalizzazioneGeografica',
                    'data-ajax-value' => true, ),
            ));
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();
            $codProvincia = $data['provincia'];
            $comune = $form->get('comune');
            $form->add('comune', \BaseBundle\Form\CommonType::entity, array(
                'label' => null,
                'mapped' => false,
                'placeholder' => '-',
                'choice_label' => 'descrizione_comune',
                'class' => 'MonitoraggioBundle\Entity\TC16LocalizzazioneGeografica',
                'query_builder' => function (TC16LocalizzazioneGeograficaRepository $er) use ($codProvincia) {
                    return $er->createQueryBuilder('u')
                                    ->where('u.codice_provincia = :codProvincia')
                                    ->setParameter('codProvincia', is_null($codProvincia) ? '' : $codProvincia);
                },
                'attr' => array(
                    'data-ajax-class' => 'TC16LocalizzazioneGeografica',
                    'data-ajax-value' => true, ),
            ));
        });

        $builder->setDataMapper($this);
    }

    public function mapFormsToData($forms, &$data) {
        $array = iterator_to_array($forms);
        $data = $array['comune']->getData();
    }

    public function mapDataToForms($data, $forms) {
        if (is_null($data)) {
            return;
        }
        foreach ($forms as $nome => $elemento) {  /* @var \Symfony\Component\Form\FormInterface $elemento */
            switch ($nome) {
                case 'provincia':
                    $provincia = new \MonitoraggioBundle\Form\Entity\Provincia($data->getCodiceProvincia(), $data->getDescrizioneProvincia(), $data->getDescrizioneRegione());
                    $elemento->setData($provincia);
                break;
                case 'regione':
                    $regione = new \MonitoraggioBundle\Form\Entity\Regione($data->getCodiceRegione(), $data->getDescrizioneRegione());
                    $elemento->setData($regione);
                break;
                case 'comune':
                    $elemento->setData($data);
                break;
            }
        }
    }

    public function __construct(EntityManager $em = null) {
        $this->em = $em;
    }

    public function configureOptions(OptionsResolver $resolver) {
        $em = &$this->em;
        $resolver->setDefaults(array(
            'data_class' => 'MonitoraggioBundle\Entity\TC16LocalizzazioneGeografica',
        ));
    }
}
