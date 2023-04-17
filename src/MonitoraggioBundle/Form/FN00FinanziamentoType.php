<?php
namespace MonitoraggioBundle\Form;

use MonitoraggioBundle\Form\BaseFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FN00FinanziamentoType extends BaseFormType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('cod_locale_progetto', self::text, array(
                    'label' => 'cod_locale_progetto',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'] ,
                ))
        ->add('cf_cofinanz', self::text, array(
                    'label' => 'cf_cofinanz',
                    'disabled' => $options['disabled'],
                    'required' => false ,
                ))
        ->add('importo', self::text, array(
                    'label' => 'importo',
                    'disabled' => $options['disabled'],
                    'required' => false ,
                ))
       
        ->add('tc33_fonte_finanziaria', self::text, array(
                    'label' => 'tc33_fonte_finanziaria',
                    'disabled' => $options['disabled'],
                    'required' => false ,
                ))
        ->add('tc35_norma', self::text, array(
                    'label' => 'tc35_norma',
                    'disabled' => $options['disabled'],
                    'required' => false ,
                ))
        ->add('tc34_delibera_cipe', self::text, array(
                    'label' => 'tc34_delibera_cipe',
                    'disabled' => $options['disabled'],
                    'required' => false ,
                ))
        ->add('tc16_localizzazione_geografica', 'MonitoraggioBundle\Form\Type\LocalizzazioneGeograficaType', array(
                    'label' => 'tc16_localizzazione_geografica',
                    'disabled' => $options['disabled'],
                    'required' => false,
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