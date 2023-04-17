<?php

namespace AuditBundle\Form\Attuazione;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class ControlloOperazioneType extends CommonType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {             
          
		
		$builder->add('spesa_irregolare_pre_contraddittorio', self::importo, array(
                "label" => "Spesa irregolare pre-contraddittorio",
				"scale" => 2,
				"currency" => "EUR",
                'required' => true, 
				"grouping" => true,
                'constraints' => array(new NotNull())
            ));  
        
		$builder->add('spesa_irregolare_post_contraddittorio', self::importo, array(
                "label" => "Spesa irregolare post-contraddittorio",
				"scale" => 2,
				"currency" => "EUR",
                'required' => true, 
				"grouping" => true,
                'constraints' => array(new NotNull()) 
            ));  
		
		$builder->add('contributo_irregolare_pre_contraddittorio', self::importo, array(
                "label" => "Contributo irregolare pre-contraddittorio",
				"scale" => 2,
				"currency" => "EUR",
                'required' => true, 
				"grouping" => true,
                'constraints' => array(new NotNull())
            ));  
        
		$builder->add('contributo_irregolare_post_contraddittorio', self::importo, array(
                "label" => "Contributo irregolare post-contraddittorio",
				"scale" => 2,
				"currency" => "EUR",
                'required' => true, 
				"grouping" => true,
                'constraints' => array(new NotNull()) 
            ));  
		
		$builder->add('spesa_cuscinetto', self::importo, array(
                "label" => "Spesa cuscinetto",
				"scale" => 2,
				"currency" => "EUR",
                'required' => true, 
				"grouping" => true,
                'constraints' => array(new NotNull())
            ));  
        
		$builder->add('contributo_pubblico_cuscinetto', self::importo, array(
                "label" => "Contributo pubblico cuscinetto",
				"scale" => 2,
				"currency" => "EUR",
                'required' => true, 
				"grouping" => true,
                'constraints' => array(new NotNull()) 
            ));  

		$builder->add('data_inizio_contraddittorio', self::birthday, array(
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',
			'required' => false
            ));   
        
		$builder->add('data_fine_contraddittorio', self::birthday, array(
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',
			'required' => false
            )); 
              
		$builder->add('tot_spesa_irregolare_pre', self::importo, array(
                "label" => "Totale spesa irregolare pre-contraddittorio",
				"scale" => 2,
				"currency" => "EUR",
                "disabled" => true,
                'required' => false, 
				"grouping" => true,
                'mapped' => false,
            ));  
		
		$builder->add('tot_contributo_irregolare_pre', self::importo, array(
                "label" => "Totale contributo irregolare pre-contraddittorio",
				"scale" => 2,
				"currency" => "EUR",
                "disabled" => true,
                'required' => false, 
				"grouping" => true,
                'mapped' => false,
            ));  
		
		$builder->add('tot_spesa_irregolare_post', self::importo, array(
                "label" => "Totale spesa irregolare post-contraddittorio",
				"scale" => 2,
				"currency" => "EUR",
                "disabled" => true,
                'required' => false, 
				"grouping" => true,
                'mapped' => false,
            ));  
		
		$builder->add('tot_contributo_irregolare_post', self::importo, array(
                "label" => "Totale contributo irregolare post-contraddittorio",
				"scale" => 2,
				"currency" => "EUR",
                "disabled" => true,
                'required' => false, 
				"grouping" => true,
                'mapped' => false,
            ));  
		
		$builder->add('spesa_sottoposta_audit', self::importo, array(
                "label" => "Spesa sottoposta a audit",
				"scale" => 2,
				"currency" => "EUR",
                "disabled" => true,
                'required' => false, 
				"grouping" => true,
                'mapped' => false,
            ));  
		
		$builder->add('tot_errore_ada', self::importo, array(
                "label" => "Totale errore AdA (contributo pubblico da detrarre in A8)",
				"scale" => 2,
				"currency" => "EUR",
                "disabled" => true,
                'required' => false, 
				"grouping" => true,
                'mapped' => false,
            ));  
        
        
		$builder->add('note', self::textarea, array(
			'label' => "Testo del report",
			'disabled' => false,
            'required' => true, 
            'constraints' => array(new NotNull()) 
		));        
        
        $builder->add('verificatore', self::entity, array(
            'class'   => "AuditBundle\Entity\Verificatore",
            "label" => "Verificatore",
            'placeholder' => '-',
            'constraints' => array(new NotNull())
        ));         
               
		$builder->add('sede_legale_controllo', self::text, array(
			'label' => "Sede legale / Unità locale",
			'disabled' => false, 
            'required' => true  ,
            'constraints' => array(new NotNull())
		));   
        
		$builder->add('data_sopralluogo', self::birthday, array(
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',
			'required' => true,
            'constraints' => array(new NotNull())
            ));       
		
        $builder->add('operazione_conclusa', self::choice, array(
            'choice_value' => array($this, "mapping"),
            'choices'  => array('Operazione conclusa' => true, 'Operazione in corso' => false),
            'placeholder' => '',
            'choices_as_values' => true,
            'label' => "Tipo di controllo", 
            'required' => true,
            'constraints' => array(new NotNull()) 
            ));        
		
		$builder->add('natura_irregolarita', self::entity, array(
			'class' => 'AuditBundle\Entity\NaturaIrregolarita',
			'placeholder' => false,
			'expanded' => false,
			'multiple' => false,
			'required' => true,
			'label' => 'Natura irregolarità'
		));
		
		$builder->add('tipo_irregolarita', self::entity, array(
			'class' => 'AuditBundle\Entity\TipoIrregolarita',
			'placeholder' => false,
			'expanded' => false,
			'multiple' => false,
			'required' => true,
			'label' => 'Tipo irregolarità'
		));

        $builder->add('salva_invia', self::salva_indietro, array('url'=>$options["url_indietro"]));       
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AuditBundle\Entity\AuditCampioneOperazione'
        ));
        
        $resolver->setRequired("url_indietro");
    }
}
