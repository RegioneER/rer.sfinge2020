<?php

namespace RichiesteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use BaseBundle\Form\CommonType;


class RichiestaCupBatchType extends CommonType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
//        $builder
//            ->add('data_risposta', 'datetime')
//            ->add('data_cancellazione', 'datetime')
//            ->add('data_creazione', 'datetime')
//            ->add('data_modifica', 'datetime')
//            ->add('creato_da')
//            ->add('modificato_da')
//            ->add('cup_batch_documento_richiesta')
//            ->add('cup_batch_documento_risposta')
//        ;
		
		$tipo_tracciato = \array_key_exists("tipo_tracciato" , $options) ? $options['tipo_tracciato'] : NULL;
		
		switch ($tipo_tracciato) {
			case "codici_cup":
				$builder->add('cupBatchDocumentoRisposta', self::documento, array('label' => false,"tipo"=>$options["CIPE_BATCH"]));
				$builder->add('salvaEsiti', self::checkbox, array ('label' => 'salva esiti'));
				break;
		
			case "scarti":
				$builder->add('cupBatchDocumentoScarto', self::documento,
				array('label' => false,"tipo"=>$options["CIPE_BATCH"]));
				$builder->add('salvaScarti', self::checkbox, array ('label' => 'salva scarti'));
				break;
			
			default:
				// utile per una eventuale maschera di edit della richiestaCupBatch
				break;
		}
		
		
		
		
		$builder->add('Salva', self::salva, array("label" => false));

    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'RichiesteBundle\Entity\RichiestaCupBatch',
			'CIPE_BATCH' => '',
			'tipo_tracciato' => ''
        ));
    }
}
