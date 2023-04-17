<?php
namespace MonitoraggioBundle\Form\Type;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\DataMapperInterface;
use \Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class GeoComuneType extends CommonType implements DataMapperInterface
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
            'query_builder' => function (EntityRepository $er) use ($data) {
                return $er->createQueryBuilder('p')
                    ->join('p.comuni','comuni')
                    ->where('comuni = :comune')
                    ->setParameter('comune', $data);
            },
            'attr' => array(
                'data-geo' => 'provincia',
            ),
        ));

        $builder->add('comune', self::entity, array(
            'class' => 'GeoBundle\Entity\GeoComune',
            'label' => 'Comune',
            'disabled' => $options['disabled'],
            'required' => !$options['disabled'] && $options['required'],
            'query_builder' => function (EntityRepository $er) use ($data) {
                return $er->createQueryBuilder('u')
                    ->where('u = :comune')
                    ->setParameter('comune', $data);
            },
            'attr' => array(
                'data-geo' => 'comune',
            ),
        ));

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($options) {
            $form = $event->getForm();
            $data = $form->getData();

            $form->add('provincia', \BaseBundle\Form\CommonType::entity, array(
                'label' => 'Provincia',
                'class' => 'GeoBundle\Entity\GeoProvincia',
                'required' => false,
                'disabled' => $options['disabled'],
                'query_builder' => function (EntityRepository $er) use ($data) {
                    return $er->createQueryBuilder('p')
                        ->join('p.comuni', 'comuni')
                        ->where('comuni = :comune')
                        ->setParameter('comune', $data);
                },
            ));
    
            $form->add('comune', \BaseBundle\Form\CommonType::entity, array(
                'class' => 'GeoBundle\Entity\GeoComune',
                'label' => 'Comune',
                'disabled' => $options['disabled'],
                'required' => !$options['disabled'] && $options['required'],
                'query_builder' => function (EntityRepository $er) use ($data) {
                    return $er->createQueryBuilder('u')
                        ->where('u = :comune')
                        ->setParameter('comune', $data);
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
            'data_class' => 'GeoBundle\Entity\GeoComune',
        ));
        $resolver->setRequired(array(
            'disabled', 'required'
        ));
    }

    /**
     * @param $geoComune \GeoBundle\Entity\GeoComune
     */
    public function mapDataToForms($geoComune, $form)
    {
        $form = iterator_to_array($form);
        if($geoComune){
            $form['comune']->setData($geoComune);
            $provincia = $geoComune->getProvincia();
            $form['provincia']->setData($provincia);
            $form['regione']->setData($provincia->getRegione());
        }
    }
    /**
     * @param data \SfingeBundle\Entity\Procedura
     */
    public function mapFormsToData($form, &$geoComune)
    {
        $form = iterator_to_array($form);
        $geoComune = $form->getData();
    }
}