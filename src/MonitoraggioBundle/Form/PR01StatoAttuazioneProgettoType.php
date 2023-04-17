<?php
namespace MonitoraggioBundle\Form;

use MonitoraggioBundle\Form\BaseFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PR01StatoAttuazioneProgettoType extends BaseFormType
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
                
                ->add('tc47_stato_progetto', self::entity, array(
                    'label' => 'tc47_stato_progetto',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'] ,
                    'class' => 'MonitoraggioBundle:TC47StatoProgetto',
                ))
        ->add('data_riferimento', self::birthday, array(
                    'label' => 'Data di riferimento',
							'disabled' => $options['disabled'],
							'required' => !$options['disabled'],
							"widget" => "single_text",
							"input" => "datetime",
							"format" => "dd/MM/yyyy",
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