<?php
namespace MonitoraggioBundle\Form;

use MonitoraggioBundle\Form\BaseFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TC36LivelloGerarchicoType extends BaseFormType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('cod_liv_gerarchico', self::text, array(
                    'label' => 'Codice livello gerarchico',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'] ,
                ))
        ->add('valore_dati_rilevati', self::textarea, array(
                    'label' => 'Valore livello gerarchico',
                    'disabled' => $options['disabled'],
                    'required' => false ,
                ))
        ->add('descrizione_codice_livello_gerarchico', self::textarea, array(
                    'label' => 'Descrizione livello gerarchico',
                    'disabled' => $options['disabled'],
                    'required' => false ,
                ))
        ->add('cod_struttura_prot', self::text, array(
                    'label' => 'Codice struttura protocollo',
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