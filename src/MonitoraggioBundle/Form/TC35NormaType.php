<?php
namespace MonitoraggioBundle\Form;

use MonitoraggioBundle\Form\BaseFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TC35NormaType extends BaseFormType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('cod_norma', self::text, array(
                    'label' => 'Codice norma',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'] ,
                ))
                ->add('tipo_norma', self::text, array(
                            'label' => 'Tipologia norma',
                            'disabled' => $options['disabled'],
                            'required' => false ,
                        ))
                ->add('descrizione_norma', self::text, array(
                            'label' => 'Descrizione norma',
                            'disabled' => $options['disabled'],
                            'required' => false ,
                        ))
                ->add('numero_norma', self::text, array(
                            'label' => 'Numero norma',
                            'disabled' => $options['disabled'],
                            'required' => false ,
                        ))
                ->add('anno_norma', self::text, array(
                            'label' => 'Anno norma',
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