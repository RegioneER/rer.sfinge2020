<?php

namespace MonitoraggioBundle\Form\Type;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\DataMapperInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class IndirizzoType extends CommonType implements DataMapperInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $data = $builder->getData();

        $builder->add('regione', self::entity, array(
            'label' => 'Regione',
            'class' => 'GeoBundle\Entity\GeoRegione',
            'required' => false,
            'disabled' => $options['disabled'],
            'attr' => array(
                'data-geo' => 'regione',
            ),
        ));

        $builder->add('provincia', self::entity, array(
            'label' => 'Provincia',
            'class' => 'GeoBundle\Entity\GeoProvincia',
            'required' => false,
            'disabled' => $options['disabled'],

            'attr' => array(
                'data-geo' => 'provincia',
            ),
        ));

        $builder->add('comune', self::entity, array(
            'disabled' => $options['disabled'],
            'required' => $options['required'],
            'label' => 'Comune',
            'class' => 'GeoBundle\Entity\GeoComune',
        ));

        $builder->add('via', self::text, array(
            'label' => 'Via',
            'disabled' => $options['disabled'],
            'required' => $options['required'],
        ));

        $builder->add('numero_civico', self::text, array(
            'label' => 'Numero civico',
            'disabled' => $options['disabled'],
            'required' => $options['required'],
        ));

        $builder->add('cap', self::text, array(
            'label' => 'CAP',
            'disabled' => $options['disabled'],
            'required' => $options['required'],
        ));

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($options) {
            $form = $event->getForm();
            //$data = $form->getData();
            $data = $event->getData();

            $form->add('provincia', \BaseBundle\Form\CommonType::entity, array(
                'label' => 'Provincia',
                'class' => 'GeoBundle\Entity\GeoProvincia',
                'required' => false,
                'disabled' => $options['disabled'],
                'query_builder' => function (EntityRepository $er) use ($data) {
                    $value = array_key_exists('regione', $data) ? $data['regione'] : null;

                    return $er->createQueryBuilder('p')
                        ->join('p.regione', 'regione')
                        ->where('regione = :regione')
                        ->setParameter('regione', $value);
                },
                'attr' => array(
                    'data-geo' => 'provincia',
                ),
            ));

            $form->add('comune', \BaseBundle\Form\CommonType::entity, array(
                'disabled' => $options['disabled'],
                'required' => $options['required'],
                'label' => 'Comune',
                'class' => 'GeoBundle\Entity\GeoComune',
                'query_builder' => function (EntityRepository $er) use ($data) {
                    $value = array_key_exists('provincia', $data) ? $data['provincia'] : null;

                    return $er->createQueryBuilder('comuni')
                        ->join('comuni.provincia', 'provincia')
                        ->where('provincia = :provincia')
                        ->setParameter('provincia', $value);
                },
            ));
        });

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $form = $event->getForm();
            $data = $event->getData();
            $provincia = is_null($data) || is_null($data->getComune()) ? null : $data->getComune()->getProvincia();

            $form->add('provincia', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', array(
            'label' => 'Provincia',
            'class' => 'GeoBundle\Entity\GeoProvincia',
            'required' => false,
            'disabled' => $options['disabled'],
            'query_builder' => function (EntityRepository $er) use ($provincia) {
                $regione = is_null($provincia) ? null : $provincia->getRegione();

                return $er->createQueryBuilder('p')
                    ->join('p.regione', 'regione')
                    ->where('regione = :regione')
                    ->setParameter('regione', $regione);
            },
            'attr' => array(
                'data-geo' => 'provincia',
            ),
        ));

            $form->add('comune', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', array(
                'disabled' => $options['disabled'],
                'required' => $options['required'],
                'label' => 'Comune',
                'class' => 'GeoBundle\Entity\GeoComune',
                'query_builder' => function (EntityRepository $er) use ($provincia) {
                    return $er->createQueryBuilder('comuni')
                        ->join('comuni.provincia', 'provincia')
                        ->where('provincia = :provincia')
                        ->setParameter('provincia', $provincia);
                },
            ));
        });

        $builder->setDataMapper($this);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
             'data_class' => 'BaseBundle\Entity\Indirizzo',
         ));
        $resolver->setRequired(array(
            'disabled', 'required',
        ));
    }

    /**
     * @param $geoComune \BaseBundle\Entity\Indirizzo
     */
    public function mapDataToForms($data, $form)
    {
        $geoComune = $data ? $data->getComune() : null;
        $provincia = $geoComune ? $geoComune->getProvincia() : null;
        $form->rewind();
        while ($form->valid()) {
            switch ($form->key()) {
                case 'regione':
                    $form->current()->setData($geoComune ? $provincia->getRegione() : null);
                    break;
                case 'provincia':
                    $form->current()->setData($provincia);
                    break;
                case 'comune':
                    $form->current()->setData($geoComune);
                    break;
                case 'via':
                    $form->current()->setData($data ? $data->getVia() : null);
                    break;
                case 'numero_civico':
                    $form->current()->setData($data ? $data->getNumeroCivico() : null);
                    break;
                case 'cap':
                    $form->current()->setData($data ? $data->getCap() : null);
                    break;
            }
            $form->next();
        }
    }

    /**
     * @param data \SfingeBundle\Entity\Procedura
     */
    public function mapFormsToData($form, &$data)
    {
        $form = iterator_to_array($form);
        $data->setComune($form['comune']->getData());
        $data->setCap($form['cap']->getData());
        $data->setNumeroCivico($form['numero_civico']->getData());
        $data->setVia($form['via']->getData());
    }
}
