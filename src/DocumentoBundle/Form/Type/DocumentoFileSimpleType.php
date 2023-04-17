<?php

namespace DocumentoBundle\Form\Type;

use BaseBundle\Form\CommonType;
use Doctrine\ORM\EntityManager;
use DocumentoBundle\Entity\TipologiaDocumento;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints as Assert;
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
class DocumentoFileSimpleType extends CommonType
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

		$service_container = $this->serviceContainer;
		$builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options, $service_container){
			$form = $event->getForm();
			$data = $event->getData();
			
			$constraints = array(new Valid(), new File());
			$required = false;
			if($options["opzionale"] === false){
				$constraints[] = new NotNull();
				$required = true;
			}

			if (is_null($data)) {
				$label = false;
				$estensione = 'pdf';
			} else {
				$tipoDocumento = $data->getTipologiaDocumento();
				$html = $service_container->get("funzioni_utili")->getEstensioniFormattate($tipoDocumento);
				$estensione = $html;
				$label = $tipoDocumento->getDescrizione();
			}

			

			$form->add('file', 'Symfony\Component\Form\Extension\Core\Type\FileType',
				array('label' =>$label,
					"required"=>$required,
					'constraints' => $constraints,
					'estensione' => $estensione
				));
		});

    }
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DocumentoBundle\Entity\DocumentoFile',
            "opzionale" => true,
            'label_salva' => 'Salva',
            'nascondi_div_container' => false, 
        ));

        $resolver->setAllowedTypes('opzionale', array('boolean'));
    }

}
