<?php
namespace MonitoraggioBundle\Form;

use MonitoraggioBundle\Form\BaseFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FN03PianoCostiType extends BaseFormType
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
        ->add('anno_piano', self::text, array(
                    'label' => 'Anno di riferimento',
                    'disabled' => $options['disabled'],
                    'required' => false ,
                ))
        ->add('imp_realizzato', self::text, array(
                    'label' => 'Importo realizzato',
                    'disabled' => $options['disabled'],
                    'required' => false ,
                ))
        ->add('imp_da_realizzare', self::text, array(
                    'label' => 'Importo da realizzare',
                    'disabled' => $options['disabled'],
                    'required' => false ,
                ))
         ->add('flg_cancellazione', self::choice, array(
                    'label' => 'Cancellato',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    'choices_as_values' => true,
                    'choices' => array(
                        'Sì' => 'S'
                    ),
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