<?php

/**
* @author lfontana
*/
namespace MonitoraggioBundle\Form;
use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MonitoraggioEsportazioneTrasferimentoType extends CommonType
{
        /**
     * {@inheritdoc}
     */
     public function buildForm(FormBuilderInterface $builder, array $options)
     {
         parent::buildForm($builder, $options);
         $builder->add('monitoraggio_configurazione', self::collection, array(
            'entry_type' => 'MonitoraggioBundle\Form\MonitoraggioConfigurazioneEsportazioneTrasferimentoType',
            'label' => false,
            'disabled' => $options['esportazioneInviata'],
         ))
         ->add('salvaAggiorna', self::submit, array(
            // 'label' => false,
            'label' => 'Salva ed aggiorna',
            'attr' => array(
                'style' => 'margin-left: 1em;',
                // 'data-confirm' => 'L\'operazione potrebbe richiede diversi minuti, sei sicuro di voler procedere?',
            ),
            'disabled' => $options['esportazioneInviata'],
        ))
        ->add('generaFile', self::submit, array(
            'label' => 'Genera file IGRUE',
            'disabled' => false,
            'attr' => array(
                'style' => 'margin-left: 1em;'
            ),
        ))
         ->add('submit',self::salva_indietro,array(
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
}
