<?php
namespace MonitoraggioBundle\Form;

use MonitoraggioBundle\Form\BaseFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FN10EconomieType extends BaseFormType
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
        ->add('importo', self::moneta, array(
                    'label' => 'Importo',
                    'disabled' => $options['disabled'],
                    'required' => false ,
                ))
   
        ->add('tc33_fonte_finanziaria', self::entity, array(
                    'label' => 'Domanda di pagamento',
                    'disabled' => $options['disabled'],
                    'required' => false ,
            'class' => 'MonitoraggioBundle:TC33FonteFinanziaria',
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