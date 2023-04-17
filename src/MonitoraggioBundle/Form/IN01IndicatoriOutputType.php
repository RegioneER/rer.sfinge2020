<?php
namespace MonitoraggioBundle\Form;

use MonitoraggioBundle\Form\BaseFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IN01IndicatoriOutputType extends BaseFormType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('cod_locale_progetto', self::text, array(
                    'label' => 'Codice locale progetto',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'] ,
                ))
        ->add('tipo_indicatore_di_output', self::choice, array(
                    'label' => 'Tipo indicatore risultato',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'],
                    'choices_as_values' => true,
                    'choices' => [
                        "Comune nazionale/comunitario" => "COM",
                        "Definito dal programma" => "DPR",
                    ],
            ))
        ->add('indicatore_id', self::entity, array(
                    'class' => 'MonitoraggioBundle\Entity\TC44_45IndicatoriOutput',
                    'label' => 'Indicatore',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'] ,
                ))
        ->add('val_programmato', self::moneta, array(
                    'label' => 'Valore programmato',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'] ,
                ))
        ->add('valore_realizzato', self::moneta, array(
                    'label' => 'Valore realizzato',
                    'disabled' => $options['disabled'],
                    'required' => false ,
                ))
        ->add('flg_cancellazione', self::choice, array(
                    'label' => 'Cancellato',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    'choices_as_values' => true,
                    'choices' => array('Sì' => 'S'),
                    'placeholder' => 'No',
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