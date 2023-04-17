<?php
namespace MonitoraggioBundle\Form;

use MonitoraggioBundle\Form\BaseFormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AP00AnagraficaProgettiType extends BaseFormType
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
        ->add('titolo_progetto', self::text, array(
                    'label' => 'Titolo progetto',
                    'disabled' => $options['disabled'],
                    'required' => false ,
                ))
        ->add('sintesi_prg', self::textarea, array(
                    'label' => 'Sintesi progetto',
                    'disabled' => $options['disabled'],
                    'required' => false ,
                ))
        ->add('cup', self::text, array(
                    'label' => 'Codice CUP',
                    'disabled' => $options['disabled'],
                    'required' => false ,
                ))
        ->add('data_inizio', self::birthday, array(
                    'label' => 'Data inizio progetto',
                    'disabled' => $options['disabled'],
                    'required' => false ,
                    "widget" => "single_text",
                    "input" => "datetime",
                    "format" => "dd/MM/yyyy",
                ))
        ->add('data_fine_prevista', self::birthday, array(
                    'label' => 'Data fine prevista del progetto',
                    'disabled' => $options['disabled'],
                    'required' => false ,
                    "widget" => "single_text",
                    "input" => "datetime",
                    "format" => "dd/MM/yyyy",
                ))
        ->add('data_fine_effettiva', self::birthday, array(
                    'label' => 'Data fine effettiva del progetto',
                    'disabled' => $options['disabled'],
                    "widget" => "single_text",
                    "input" => "datetime",
                    "format" => "dd/MM/yyyy",
                    'required' => false ,
                ))
        ->add('codice_proc_att_orig', self::text, array(
                    'label' => 'Codice procedura attivazione originaria',
                    'disabled' => $options['disabled'],
                    'required' => false ,
                ))
       
        ->add('tc5_tipo_operazione', self::entity, array(
                    'label' => 'Tipo operazione',
                    'disabled' => $options['disabled'],
                    'required' => false,
                    "class" => "MonitoraggioBundle\Entity\TC5TipoOperazione",
                ))
        ->add('tc6_tipo_aiuto', self::entity, array(
                    'label' => 'Tipo di aiuto',
                    'disabled' => $options['disabled'],
                    "class" => "MonitoraggioBundle\Entity\TC6TipoAiuto",
                    'required' => false ,
                ))
        ->add('tc48_tipo_procedura_attivazione_originaria', self::entity, array(
                    'label' => 'Tipo procedura attivazione originaria',
                    'disabled' => $options['disabled'],
                    "class" => "MonitoraggioBundle\Entity\TC48TipoProceduraAttivazioneOriginaria",
                    'required' => false ,
                ))
         ->add('flg_cancellazione', self::choice, array(
                    'label' => 'Flag cancellazione',
                    'disabled' => $options['disabled'],
                    'required' => false ,
                    'choices' => array('Sì' => 'S'),
                    'choices_as_values' => true,
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