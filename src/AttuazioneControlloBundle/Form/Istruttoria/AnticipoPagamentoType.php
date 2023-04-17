<?php

namespace AttuazioneControlloBundle\Form\Istruttoria;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class AnticipoPagamentoType extends CommonType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

		
		$builder->add('proponente', self::entity, array(
            'class'   => 'RichiesteBundle\Entity\Proponente',
            'label' => 'Proponente',
			'choice_label' => 'getDenominazioneAcronimo',
            'placeholder' => '-',
			'required' => true,
			'choices' => $options['proponenti'],
			'constraints' => array(new NotNull())
        ));
		
		$builder->add('importo_anticipo', self::importo, array("label" => "Importo anticipo",
					  "required" => true, 'constraints' => array(new NotNull()),"currency" => "EUR", "grouping" => true));
		
		$builder->add('numero_atto', self::text, array(
            'label' => 'Numero atto',
			'constraints' => array(new NotNull())
        ));
		
		
		$builder->add('data_atto', self::birthday, array(
            'label' => 'Data Atto',
			'widget' => 'single_text',
			'input' => 'datetime',
			'format' => 'dd/MM/yyyy',      
			'required' => true
        ));
		
		
        $builder->add("pulsanti", self::salva_indietro, array("url" => $options["url_indietro"]));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AttuazioneControlloBundle\Entity\Istruttoria\RipartizioneImportiPagamento'
        ));
        $resolver->setRequired("url_indietro");
//		$resolver->setRequired("importo");
//		$resolver->setRequired("data_atto");
//		$resolver->setRequired("numero_atto");
		$resolver->setRequired("proponenti");
    }
}
