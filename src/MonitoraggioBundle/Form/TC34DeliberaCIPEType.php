<?php
namespace MonitoraggioBundle\Form;

use MonitoraggioBundle\Form\BaseFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TC34DeliberaCIPEType extends BaseFormType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('cod_del_cipe', self::text, array(
                    'label' => 'Codice delibera CIPE',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'] ,
                ))
        ->add('numero', self::text, array(
                    'label' => 'Numero delibera CIPE',
                    'disabled' => $options['disabled'],
                    'required' => false ,
                ))
        ->add('anno', self::text, array(
                    'label' => 'Anno delibera CIPE',
                    'disabled' => $options['disabled'],
                    'required' => false ,
                ))
        ->add('tipo_quota', self::text, array(
                    'label' => 'Tipologia Quota',
                    'disabled' => $options['disabled'],
                    'required' => false ,
                ))
        ->add('descrizione_quota', self::textarea, array(
                    'label' => 'Descrizione Quota',
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