<?php

namespace RichiesteBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class PrioritaProponenteType extends RichiestaType {

	protected $container;

	public function __construct($container) {
		$this->container = $container;
	}

	public function buildForm(FormBuilderInterface $builder, array $options) {

		$em = $this->container->get("doctrine")->getManager();
		$laboratori = $options['laboratori'];
		
		$builder->add('sistema_produttivo', self::entity, array('class' => 'SfingeBundle\Entity\SistemaProduttivo',
			'choice_label' => function ($sistema) {
				return $sistema->getDescrizione();
			},
			'query_builder' => function(\Doctrine\ORM\EntityRepository $er) use ($laboratori) {
				return $er->createQueryBuilder("ts")
								->where("coalesce(ts.laboratori,0) = coalesce(:laboratori,0)")
								->setParameter("laboratori", $laboratori)
								->orderBy("ts.codice", "ASC");
			},
			'placeholder' => '-',
			'required' => true,
			'label' => 'Area di specializzazione',
		));

		$data = isset($options["request_data"]) && $options["request_data"]->has($this->getName()) ? $options["request_data"]->get($this->getName()) : array();

		$formModifier = function (FormInterface $form, $sistemaProduttivo = null) use ($em, $data) {

			if (isset($data["sistema_produttivo"])) {
				$sistemaProduttivo = $em->getRepository("SfingeBundle\Entity\SistemaProduttivo")->find($data["sistema_produttivo"]);
			}

			$orientamentiTematici = null === $sistemaProduttivo ? array() : $sistemaProduttivo->getOrientamentiTematici();

			$form->add('orientamento_tematico', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', array(
				'class' => 'SfingeBundle\Entity\OrientamentoTematico',
				'choice_label' => 'descrizione',
				'placeholder' => '-',
				'required' => true,
				'label' => 'Orientamento tematico',
				'choices' => $orientamentiTematici,
			));
		};

		$formModifier2 = function (FormInterface $form, $orientamentoTematico = null) use ($em, $data) {

			if (isset($data["orientamento_tematico"])) {
				$orientamentoTematico = $em->getRepository("SfingeBundle\Entity\OrientamentoTematico")->find($data["orientamento_tematico"]);
			}

			$prioritaTecnologiche = null === $orientamentoTematico ? array() : $orientamentoTematico->getPrioritaTecnologiche();

			$form->add('priorita_tecnologiche', 'Symfony\Bridge\Doctrine\Form\Type\EntityType', array(
				'class' => 'SfingeBundle\Entity\PrioritaTecnologica',
				'choice_label' => 'descrizione',
				'placeholder' => '-',
				'required' => true,
				'label' => 'Priorità tecnologiche',
				'multiple' => true,
				'choices' => $prioritaTecnologiche,
			));
		};

		$builder->addEventListener(
				FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($formModifier, $formModifier2, $options) {
			$data = $event->getData();
			$formModifier($event->getForm(), $data->getSistemaProduttivo());
			if ($options['has_priorita_tecnologiche'] == 1) {
				$formModifier2($event->getForm(), $data->getOrientamentoTematico());
			}
		}
		);
	}

	public function configureOptions(OptionsResolver $resolver) {
		$resolver->setDefaults(array(
			'data_class' => 'RichiesteBundle\Entity\PrioritaProponente',
			'readonly' => false,
			'request_data' => array()
		));

		$resolver->setRequired("readonly");
	}

}
