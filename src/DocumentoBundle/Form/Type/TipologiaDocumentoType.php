<?php

namespace DocumentoBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use BaseBundle\Form\CommonType;

class TipologiaDocumentoType extends CommonType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
        
         
        $builder->add('codice', self::text, array("required" => true, "constraints" => array(new \Symfony\Component\Validator\Constraints\NotNull())))            
                ->add('descrizione', self::textarea, array("required" => true, "constraints" => array(new \Symfony\Component\Validator\Constraints\NotNull())))
                ->add('autocertificazione', null, array("required" => false))
                ->add('firma_digitale', null, array("required" => false))
                ->add('con_scadenza', 'checkbox', array("label" =>"Documento con scadenza", "required" => false))
                ->add('durata_validita', self::numero, array("label" => "Durata validità", "required" => false))
                ->add('dimensione_massima', self::integer, array("label" => "Dimensione massima (MB)", "required" => true, "constraints" => array(new \Symfony\Component\Validator\Constraints\NotNull())))
                ->add('mime_ammessi', self::textarea, array("label" => "Mime ammessi", "required" => true, "constraints" => array(new \Symfony\Component\Validator\Constraints\NotNull())))
                ->add('obbligatorio', null, array("required" => false))
                ->add('abilita_duplicati', null, array("required" => false))  
                ->add('tipologia', null, array("required" => true, "constraints" => array(new \Symfony\Component\Validator\Constraints\NotNull())))
                ->add('prefix', null, array("required" => true, "constraints" => array(new \Symfony\Component\Validator\Constraints\NotNull())))
                ;
        
        $builder->add('pulsanti', self::salva_indietro, array("url"=>$options["url_indietro"]));
       
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DocumentoBundle\Entity\TipologiaDocumento',
        ));
        
        $resolver->setRequired("url_indietro");
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'tipologiadocumento';
    }
}
