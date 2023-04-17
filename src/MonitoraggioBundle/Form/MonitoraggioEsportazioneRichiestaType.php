<?php

/**
 * @author lfontana
 */

namespace MonitoraggioBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\DataMapperInterface;


class MonitoraggioEsportazioneRichiestaType extends CommonType implements DataMapperInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add('monitoraggio_configurazione', self::collection, array(
            'entry_type' => 'MonitoraggioBundle\Form\MonitoraggioConfigurazioneEsportazioneRichiestaType',
            'disabled' => $options['esportazioneInviata'],
            'label' => false,
         ))
         ->add('salvaAggiorna', self::submit, array(
            'label' => 'Salva ed aggiorna',
            'disabled' => $options['esportazioneInviata'],
            'attr' => array(
                'style' => 'margin-left: 1em;',
                // 'data-confirm' => 'L\'operazione potrebbe richiede diversi minuti, sei sicuro di voler procedere?',
            ),
        ))
        ->add('generaFile', self::submit, array(
            'label' => 'Genera file IGRUE',
            'disabled' => false,
            'attr' => array(
                'style' => 'margin-left: 1em;',
            ),
        ))
         ->add('submit', self::salva_indietro, array(
            'url' => $options['url_indietro'],

            'disabled' => $options['esportazioneInviata'],
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
            'data_class' => 'MonitoraggioBundle\Entity\MonitoraggioEsportazione',
            'esportazioneInviata' => false,
        ))
        ->setRequired('url_indietro');
    }

    public function mapDataToForms($data, $form)
    {
        foreach ($form as $key => $element) {

            if ($key == 'monitoraggio_configurazione') {
                $element->setData(new \Doctrine\Common\Collections\ArrayCollection($data->getmonitoraggioConfigurazione()->slice(0, 10)));
            }

        }
        //$form = iterator_to_array($form);

        // $form['monitoraggio_configurazione'] = new \Doctrine\Common\Collections\ArrayCollection($data->getmonitoraggioConfigurazione()->slice(0,10));

    }

    public function mapFormsToData($form, &$data)
    {
        $form = iterator_to_array($form);
        $data->setmonitoraggioConfigurazione($form['monitoraggio_configurazione']);

    }

}
