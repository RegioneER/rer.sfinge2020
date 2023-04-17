<?php
namespace MonitoraggioBundle\Form;

use MonitoraggioBundle\Form\BaseFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TC44IndicatoriOutputComuniType extends BaseFormType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('cod_indicatore', self::text, array(
                    'label' => 'Codice indicatore output',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'] ,
                ))
        ->add('descrizione_indicatore', self::textarea, array(
                    'label' => 'Descrizione indicatore output',
                    'disabled' => $options['disabled'],
                    'required' => false ,
                ))
        ->add('unita_misura', self::text, array(
                    'label' => 'Unità di misura',
                    'disabled' => $options['disabled'],
                    'required' => false ,
                ))
        ->add('desc_unita_misura', self::text, array(
                    'label' => 'Descrizione unità di misura',
                    'disabled' => $options['disabled'],
                    'required' => false ,
                ))
        ->add('flag_calcolo', self::text, array(
                    'label' => 'Flag calcolo',
                    'disabled' => $options['disabled'],
                    'required' => false ,
                ))
        ->add('fonte_dato', self::text, array(
                    'label' => 'Fonte del dato',
                    'disabled' => $options['disabled'],
                    'required' => false ,
                ))
        
                ->add('submit',self::salva_indietro, array(
                    "url" => $options["url_indietro"], 
                    'disabled' => false,
                ));
    }    
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
    }
}