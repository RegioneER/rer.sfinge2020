<?php

/**
 * @author lfontana
 */
namespace MonitoraggioBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class MonitoraggioConfigurazioneEsportazioneTavoleType extends CommonType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $data = $builder->getForm()->getData();
        //  var_dump($data);
        //  die();
        //  if(is_null($data->getFlagConfermaEsportazione())){
        //      $data->setFlagConfermaEsportazione( $data->getFlagEsportazione());
        //  }
        $builder->add('flag_conferma_esportazione', self::choice, array(
            'label' => false,
            'required' => true,
            'choice_translation_domain' => false,
            'choices_as_values' => true,
            'choices' => array(
                'No' => 0,
                'Sì' => 1,
            ),
        ))


            ->addEventListener(FormEvents::POST_SET_DATA, function ($event) {
            $data = $event->getData();
            if (is_null($data->getFlagConfermaEsportazione())) {
                $data->setFlagConfermaEsportazione($data->getFlagEsportazione());
                $event->getForm()->add('flag_conferma_esportazione', \BaseBundle\Form\CommonType::choice, array(
                    'label' => false,
                    'required' => true,
                    'choice_translation_domain' => false,
                    'choices_as_values' => true,
                    'choices' => array(
                        'No' => 0,
                        'Sì' => 1,
                    )
                ));
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
            'data_class' => 'MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneTavole',
        ));
    }

}
