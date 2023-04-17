<?php

namespace RichiesteBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use RichiesteBundle\Ricerche\RicercaRichiestaLatoPA;
use Doctrine\ORM\EntityManager;

class RicercaRichiestaType extends CommonType {

	/**
	 * @var EntityManager
	 */
	protected $em;

	function __construct($doctrine) {
		$this->em = $doctrine->getManager();
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {

		parent::buildForm($builder, $options);

		$builder->add('stato', self::entity, array(
			'class' => 'BaseBundle:StatoRichiesta',
			'placeholder' => '-',
			'required' => false,
		));


		$ricerca = $builder->getData();	/** @var \RichiesteBundle\Form\Entity\RicercaRichiesta $ricerca */
		$procedure = $ricerca->getQueryRicercaProcedura($this->em, $options);

		$builder->add('procedura', self::entity, array(
			'choices' => $procedure,
			'class' => 'SfingeBundle:Procedura',
			'placeholder' => '-',
			'required' => false,
		));
		
		if($ricerca instanceof RicercaRichiestaLatoPA ) {
			$builder->add('finestraTemporale', self::choice, array(
				'required' => false,
				'label' => 'Finestra Temporale',
				'choices_as_values' => true,
				'choices' => [
					'Prima' => '1',
					'Seconda' => '2',
                    'Terza' => '3',
                    'Quarta' => '4',
                    'Quinta' => '5',
                    'Sesta' => '6',
					]
			));			
		} 

		$builder->add('titoloProgetto', self::text, array('required' => false, 'label' => 'Titolo progetto'));
		$builder->add('protocollo', self::text, array('required' => false, 'label' => 'Protocollo'));
		$builder->add('ragioneSocialeProponente', self::text, array('required' => false, 'label' => 'Ragione sociale proponente'));
		$builder->add('codiceFiscaleProponente', self::text, array('required' => false, 'label' => 'Codice fiscale proponente'));
	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'RichiesteBundle\Form\Entity\RicercaRichiesta',
		));
	}

}
