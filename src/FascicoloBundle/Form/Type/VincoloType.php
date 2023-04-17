<?php

namespace FascicoloBundle\Form\Type;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\CallbackTransformer;

class VincoloType extends CommonType {
	
	protected $container;

	public function __construct($container) {
		$this->container = $container;
	}
	
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		$id_tipo_campo = $options["id_tipo_campo"];
		$builder->add('tipoVincolo', self::entity, array('class' => 'FascicoloBundle\Entity\TipoVincolo',
			'query_builder' => function(\Doctrine\ORM\EntityRepository $er) use ($id_tipo_campo) {
				return $er->createQueryBuilder("tp")
								->leftJoin("tp.tipiCampi", "tc")
								->where("tc.id=" . $id_tipo_campo);
			},
			'choice_label' => 'descrizione', 
			'required' => true, 
			'label' => 'Tipo',
			'read_only'=> !$options["creazione"],
			'disabled'=> !$options["creazione"]));
					
		$tipoVincolo = $builder->getData()->getTipoVincolo();
		
		if (!is_null($tipoVincolo) && !is_null($tipoVincolo->getCodice())) {
			$servizio = $this->container->get("fascicolo.vincolo.".$tipoVincolo->getCodice());
			$servizio->addTypeParameters($builder);	
		}
		
		$builder->add('submit', self::submit, array('label' => 'Salva'));
    }
		
	public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver) {
		parent::configureOptions($resolver);
		$resolver->setDefaults(array(
			'data_class' => 'FascicoloBundle\Entity\Vincolo'
		));
		
		$resolver->setRequired("id_tipo_campo");
		$resolver->setRequired("creazione");
	}
}