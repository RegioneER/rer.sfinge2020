<?php

namespace DocumentoBundle\Form\Type;

use BaseBundle\Form\MyFormType;
use BaseBundle\Form\CommonType;
use Doctrine\ORM\EntityManager;
use DocumentoBundle\Entity\TipologiaDocumento;
use DocumentoBundle\Form\Transformer\CfTransformer;
use DocumentoBundle\Validator\Constraints\ValidaDocumento;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\DependencyInjection\ContainerInterface as Container;

/**
 * Type che mappa un file da caricare.
 *
 * Nelle $options occorre indicare una array lista_tipi che indica quali sono i tipi di file accettati nella form
 *
 * Nel caso si voglia caricare un file con un tipo ben definito basta mettere una sola voce
 *
 * Class DocumentoFileType
 * @package DocumentoBundle\Form\Type
 */
class DocumentoFileType extends CommonType
{
    /**
     * @var EntityManager
     */
    protected $entityManager;
    protected $serviceContainer;

    public function __construct(Container $serviceContainer)
    {
        $this->entityManager = $serviceContainer->get("doctrine");
        $this->serviceContainer =$serviceContainer;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $lista_tipi = $options["lista_tipi"];
        $tipo = $options["tipo"];

        if(is_null($lista_tipi) && is_null($tipo)){
            throw new \Exception("E' necessario indicare tra le opzioni un tipo o una lista dei tipi");
        }

        $constraints = array(new Valid());
        $required = false;
        if($options["opzionale"] === false){
            $constraints[] = new NotNull();
            $required = true;
        }

        if(!is_null($lista_tipi) && is_array($lista_tipi) && count($lista_tipi)>0){
            
            //se ne ho piu di uno metto la select con i tipi supportati
            $builder->add('tipologia_documento', self::entity, array(
                'class' => 'DocumentoBundle:TipologiaDocumento',
                'choice_label' => function ($documento) {
                    // $obbligatorio = ($documento->getObbligatorio() == 1 ? ' (Obbligatorio)' : '');
                    return $documento->getDescrizione(); //.' '.$obbligatorio;
                },
                //'choice_label' => 'descrizione',
                'choices' => $lista_tipi,
                "required" => true,
                'placeholder' => '-',
				'constraints' => array(new NotNull()),
				'attr' => array('class' => 'select_tipologia_documento')
            ));

            $builder->add('file', self::file ,
                array('label' =>"Carica documento",
                    "required"=>$required,
                    'constraints' => $constraints
                    ));
            
            $builder->add("cf_firmatario", self::hidden,array("data"=>$options["cf_firmatario"]));
            $builder->get("cf_firmatario")->addModelTransformer(new CfTransformer($this->serviceContainer->get("funzioni_utili")));

            return;
        }elseif(!is_null($lista_tipi) && is_array($lista_tipi) && count($lista_tipi)==0 && is_null($tipo)){
            throw new \Exception("E' necessario indicare almeno un tipo nella lista dei tipi");
        }

        if(!is_null($tipo)){

            $tipoDocumento = $this->getTipologiaDocumento($tipo);

            $html = $this->serviceContainer->get("funzioni_utili")->getEstensioniFormattate($tipoDocumento);

            $builder->add('file', self::file,
                array('label' =>$tipoDocumento->getDescrizione(),
                    "required"=>$required,
                    'constraints' => $constraints,
                    'estensione' => $html
                ));

            $builder->add('tipologia_documento', self::entity_hidden,
                array(
                    'class' => "DocumentoBundle\Entity\TipologiaDocumento",
                    "data" => $tipoDocumento
                )
            );

            $builder->add("cf_firmatario", self::hidden,array("data"=>$options["cf_firmatario"]));
            $builder->get("cf_firmatario")->addModelTransformer(new CfTransformer($this->serviceContainer->get("funzioni_utili")));
        }

    }
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DocumentoBundle\Entity\DocumentoFile',
            'tipo' => null,
            'lista_tipi' => array(),
            'cf_firmatario' => "",
            "opzionale" => false,
            'label_salva' => 'Salva',
            'nascondi_div_container' => false
        ));

        $resolver->setAllowedTypes('lista_tipi', array('null','array'));
        $resolver->setAllowedTypes('tipo', array('null','string',"DocumentoBundle\Entity\TipologiaDocumento"));
        $resolver->setAllowedTypes('cf_firmatario', array('null','string','array'));
        $resolver->setAllowedTypes('opzionale', array('boolean'));

        $resolver->setRequired("label_salva");

    }


    private function getTipologiaDocumento($valore){

        if(is_string($valore)){
            //suppongo che mi sia stato passato il codice del tipo di documento
            $tipoDocumento = $this->entityManager->getRepository("DocumentoBundle:TipologiaDocumento")->findOneByCodice($valore);
            return $tipoDocumento;

        }

        if( $valore instanceof TipologiaDocumento){
            return $valore;
        }

        throw new \Exception("Il tipo indicato non è un istanza di TipologiaDocumento");
    }

}
