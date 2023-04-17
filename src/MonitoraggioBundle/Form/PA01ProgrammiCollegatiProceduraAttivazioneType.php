<?php
namespace MonitoraggioBundle\Form;

use MonitoraggioBundle\Form\BaseFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PA01ProgrammiCollegatiProceduraAttivazioneType extends BaseFormType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('cod_proc_att', self::text, array(
                    'label' => 'Codice procedura attivazione',
                    'disabled' => $options['disabled'],
                    'required' => !$options['disabled'] ,
                ))
                ->add('tc4_programma', self::entity, array(
                    'label' => 'Programma',
                    'disabled' => $options['disabled'],
                    'required' => false ,
                    'class' => 'MonitoraggioBundle\Entity\TC4Programma',
                ))
        ->add('importo', self::moneta, array(
                    'label' => 'Importo',
                    'disabled' => $options['disabled'],
                    'required' => false ,
                ))
         ->add('flg_cancellazione', self::choice, array(
                    'label' => 'Flag cancellazione',
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