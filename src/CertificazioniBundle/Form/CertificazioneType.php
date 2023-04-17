<?php

namespace CertificazioniBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Regex;

class CertificazioneType extends CommonType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('numero', self::text, array(
            "label" => "Numero",
            "required" => true,
            'constraints' => array(new NotNull(), new Regex(array("pattern" => "/^M{0,4}(CM|CD|D?C{0,3})(XC|XL|L?X{0,3})(IX|IV|V?I{0,3})$/", "message" => "Questo valore non è un numero romano valido")))
        ));
        
        $data_corrente = new \DateTime();
        
        $anni = range(2016, 2024);
               
        $builder->add('anno', self::choice, array(
            'label' => 'Anno', 
            'choices'  => array_combine($anni, $anni), 
            'choices_as_values' => true, 
            'expanded' => false, 
            'required' => true, 
            'placeholder' => "-",
            'constraints' => array(new NotNull())));
        
		$builder->add('anno_contabile', self::integer, array(
            "label" => "Anno contabile",
            "required" => true,
            'constraints' => array(new NotNull())
        ));

        $builder->add('tipologia_certificazione', self::entity,  array('class' => 'CertificazioniBundle\Entity\TipologiaCertificazione',
            'choice_label' => function ($tipologia) {
                return $tipologia->getDescrizione();
            },
            'placeholder' => '-',
            'required' => true,
            'label' => 'Tipologia'
        )); 

        $builder->add("pulsanti", self::salva_indietro, array("url" => $options["url_indietro"]));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CertificazioniBundle\Entity\Certificazione'
        ));
        
        $resolver->setRequired("url_indietro");
    }
}
