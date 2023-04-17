<?php

namespace MonitoraggioBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use AttuazioneControlloBundle\Entity\SoggettiCollegati;

class SoggettoCollegatoType extends CommonType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        $data = $builder->getData();    /** @var SoggettiCollegati $data */
        $soggetto = $data->getSoggetto();

        $builder->add('tc24_ruolo_soggetto', self::entity, array(
            'label' => 'Ruolo soggetto',
            'required' => true,
            'disabled' => $options['ruolo_lettura'],
            'class' => 'MonitoraggioBundle\Entity\TC24RuoloSoggetto',
        ))
        ->add('soggetto', self::entity, array(
            'required' => true,
            'disabled' => $options['ruolo_lettura'],
            'class' => 'SoggettoBundle\Entity\Soggetto',
            'query_builder' => function (EntityRepository $er) use ($soggetto) {
                return $er->createQueryBuilder('soggetto')
                ->where('soggetto = :soggetto')
                ->setParameter('soggetto', $soggetto);
            },
        ))
        ->add('cod_uni_ipa', self::text, array(
            'label' => 'Codice UNI IPA',
            'required' => \is_null($soggetto) ? false : $soggetto->isSoggettoPubblico(),
            'disabled' => $options['ruolo_lettura'],
        ))
        ->add('note', self::textarea, array(
            'required' => false,
            'disabled' => $options['ruolo_lettura'],
        ))

        ->add('submit', self::salva_indietro, array(
            'url' => $options['url_indietro'],
            'disabled' => $options['ruolo_lettura'],
        ))

        // ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event){
        //     $form = $event->getForm();
        //     $d = $event->getData();

        //     $form->add('soggetto', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', array(
        //         'required' => true,
        //         'class' => 'SoggettoBundle\Entity\Soggetto',
        //     ));
        // })

        ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            /** @var SoggettiCollegati $data */
            $data = $event->getData();
            $id_soggetto = \array_key_exists('soggetto', $data) ? $data['soggetto'] : null;
            $form->add('soggetto', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', array(
                'required' => true,
                'class' => 'SoggettoBundle\Entity\Soggetto',
                'query_builder' => function (EntityRepository $er) use ($id_soggetto) {
                    return $er->createQueryBuilder('soggetto')
                    ->where('soggetto.id = :soggetto_id')
                    ->setParameter('soggetto_id', $id_soggetto);
                },
            ));
        });
    }

    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
            'data_class' => 'AttuazioneControlloBundle\Entity\SoggettiCollegati',
        ))
        ->setRequired('url_indietro')
        ->setRequired('ruolo_lettura');
    }
}
