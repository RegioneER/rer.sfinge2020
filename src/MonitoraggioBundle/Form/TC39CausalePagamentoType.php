<?php
namespace MonitoraggioBundle\Form;

use MonitoraggioBundle\Form\BaseFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TC39CausalePagamentoType extends BaseFormType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('causale_pagamento', self::text, array(
                    'label' => 'Codice causale pagamento',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'] ,
                ))
        ->add('descrizione_causale_pagamento', self::text, array(
                    'label' => 'Descrizione causale pagamento',
                    'disabled' => $options['disabled'],
                    'required' => false ,
                ))
        ->add('tipologia_pagamento', self::text, array(
                    'label' => 'Tipologia del pagamento',
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