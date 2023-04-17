<?php
namespace MonitoraggioBundle\Form;

use MonitoraggioBundle\Form\BaseFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TC37VoceSpesaType extends BaseFormType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('voce_spesa', self::text, array(
                    'label' => 'Codice voce spesa',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'] ,
                ))
        ->add('descrizione_voce_spesa', self::textarea, array(
                    'label' => 'Descrizione voce spesa',
                    'disabled' => $options['disabled'],
                    'required' => false ,
                ))
        ->add('codice_natura_cup', self::text, array(
                    'label' => 'Codice natura CUP',
                    'disabled' => $options['disabled'],
                    'required' => false ,
                ))
        ->add('descrizionenatura_cup', self::textarea, array(
                    'label' => 'Descrizione natura CUP',
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