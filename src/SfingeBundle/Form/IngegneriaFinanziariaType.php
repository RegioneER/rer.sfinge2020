<?php

namespace SfingeBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IngegneriaFinanziariaType extends ProceduraType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {

		parent::buildForm($builder, $options);

		$disabled = $options["disabled"];
		
		$builder->add('tipo_procedura_monitoraggio', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', array(
			'class' => 'SfingeBundle:TipoProceduraMonitoraggio',
			'choice_label' => 'descrizione',
			'required' => true,
			'disabled' => $disabled,
			'placeholder' => '-',
		));
		
		$builder->add('tipi_operazioni', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', array(
			'class' => 'SfingeBundle:TipoOperazione',
			'choice_label' => 'descrizione',
			'required' => true,
			'disabled' => $disabled,
			'placeholder' => '-',
			'multiple' => true,
		));

		$builder->add('data_convenzione', self::birthday, array(
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',
			'required' => true,
			'disabled' => $disabled,
			'label' => 'Data convenzione',
		));

		$builder->add('documento_convenzione', self::documento,
			array('label' => false, 'tipo'=>$options['TIPOLOGIA_DOCUMENTO'], 'disabled' => $disabled, 'opzionale' => $options['documento_opzionale']));

		$builder->add('data_programma_attivita', self::birthday, array(
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',
			'required' => true,
			'disabled' => $disabled,
			'label' => 'Data programma di attività',
		));

		$builder->add('pulsanti',self::salva_indietro,array("url"=>$options["url_indietro"], 'disabled' => $disabled));
	}
	
	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'SfingeBundle\Entity\IngegneriaFinanziaria',
			'dataAsse' => NULL,
			'dataObiettivoSpecifico' =>NULL,
			'TIPOLOGIA_DOCUMENTO' => '',
			'documento_opzionale' => false,
			'assi' => array(),
			'em' => null,
		));
		$resolver->setRequired("url_indietro");
	}

}
