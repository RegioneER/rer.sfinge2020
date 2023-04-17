<?php
namespace MonitoraggioBundle\Form;

use MonitoraggioBundle\Form\BaseFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TC46FaseProceduraleType extends BaseFormType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('cod_fase', self::text, array(
                    'label' => 'Codice fase',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'] ,
                ))
        ->add('descrizione_fase', self::textarea, array(
                    'label' => 'Descrizione fase',
                    'disabled' => $options['disabled'],
                    'required' => false ,
                ))
        ->add('codice_natura_cup', self::text, array(
                    'label' => 'Codice natura CUP',
                    'disabled' => $options['disabled'],
                    'required' => false ,
                ))
        ->add('descrizione_natura_cup', self::textarea, array(
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