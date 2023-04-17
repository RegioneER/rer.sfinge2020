<?php

namespace SfingeBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AcquisizioniType extends ProceduraType {

	/**
	 * @param FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(FormBuilderInterface $builder, array $options) {

		parent::buildForm($builder, $options);

		$read_only = $options["readonly"];
		$disabled = $options["readonly"];
		$em = $options["em"];

		if ($read_only == true) {
			$attr = array('readonly' => 'readonly');
		} else {
			$attr = array();
		}     
        
		$builder->add('tipo_procedura_monitoraggio', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', array(
			'class' => 'SfingeBundle:TipoProceduraMonitoraggio',
			//'choices' => $em->getRepository("SfingeBundle\Entity\TipoProceduraMonitoraggio")->findBy(array("codice" => "ASSISTENZA_TECNICA")),
			'choice_label' => 'descrizione',
			'required' => true,
			'disabled' => $disabled,
			'placeholder' => '-',
		));
		
		$builder->add('tipi_operazioni', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', array(
			'class' => 'SfingeBundle:TipoOperazione',
			'choice_label' => 'descrizione',
            'label' => "Tipo operazione",
			'required' => true,
			'disabled' => $disabled,
			'placeholder' => '-',
			'multiple' => false,
		));


		$builder->add('pulsanti', self::salva_indietro, array("url" => $options["url_indietro"], 'disabled' => $disabled));
	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'SfingeBundle\Entity\Acquisizioni',
			'readonly' => false,
			'dataAsse' => NULL,
			'dataObiettivoSpecifico' => NULL,
			'TIPOLOGIA_DOCUMENTO' => '',
			'documento_opzionale' => false,
			'assi' => array()
		));
		$resolver->setRequired("readonly");
		$resolver->setRequired("em");
		$resolver->setRequired("url_indietro");
	}

}
