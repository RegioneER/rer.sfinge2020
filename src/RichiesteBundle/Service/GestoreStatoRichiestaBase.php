<?php

namespace RichiesteBundle\Service;

use BaseBundle\Service\BaseService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\HttpFoundation\Response;
use RichiesteBundle\Entity\Proponente;
use RichiesteBundle\Entity\VocePianoCosto;

class GestoreStatoRichiestaBase extends BaseService implements IGestoreStatoRichiesta
{
	/**
	 * @var Richiesta $richiesta
	 */
	protected $richiesta;


	public function __construct(ContainerInterface $container, Richiesta $richiesta)
	{
		parent::__construct($container);
		$this->richiesta = $richiesta;
	}

    public function visualizzaPianoCosti(Proponente $proponente): Response{
		return $this->render('RichiesteBundle:StatoRichiesta:pianoCosti.html.twig', [
			'proponente' => $proponente,
			'piano_costi' => $this->getVistaPianoCosti($proponente),
			'annualita' => $this->getAnnualita($proponente)
		]);
	}

	public function getVociMenu(): array{
		$voci =[
			[
				'path' => $this->generateUrl('piano_costi_ammesso', ['id_proponente' => $this->richiesta->getMandatario()->getId()]),
				'label' => 'Piano costi ammesso'
			]
		];

		return $voci;
	}

	protected function getVistaPianoCosti(Proponente $proponente): array{
		$sezioni  = [];
		/** @var VocePianoCosto $voce */
		foreach ($proponente->getVociPianoCosto() as $voce) {
			$sezione = $voce->getPianoCosto()->getSezionePianoCosto()->getTitoloSezione();
			if(! isset($sezioni[$sezione])){
				$sezioni[$sezione] = [];
			}
			$sezioni[$sezione][] = $voce;
		}

		return $sezioni;
	}

	protected function getAnnualita(Proponente $proponente): array {
		$gestore = $this->container->get('gestore_piano_costo')->getGestore($this->richiesta->getProcedura());

		return $gestore->getAnnualita($proponente);
	}
}