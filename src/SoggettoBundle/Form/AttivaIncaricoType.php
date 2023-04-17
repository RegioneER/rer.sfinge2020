<?php
 
namespace SoggettoBundle\Form;
 
use BaseBundle\Form\CommonType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
 
class AttivaIncaricoType extends CommonType {
 
    public function buildForm(FormBuilderInterface $builder, array $options) {
 
        $builder->add('data_scadenza', self::text, array(
        ));

        $builder->add('data_scadenza', self::birthday, array(
            'widget' => 'single_text',
            'input' => 'datetime',
            'format' => 'dd/MM/yyyy',
            'required' => true,
            'disabled' => false,
            "label" => "Data di Scadenza Carta d'identità"
            ));

        $builder->add('pulsanti', self::salva_indietro, array("url"=>$options["url_indietro"], "label_salva"=>"Avanti"));
 
    }
     
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            //'data_class' => 'SoggettoBundle\Entity\IncaricoPersona',
 
        ));
        $resolver->setRequired("url_indietro");
    }
 
}