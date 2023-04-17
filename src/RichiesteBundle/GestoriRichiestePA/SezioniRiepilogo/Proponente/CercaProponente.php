<?php

namespace RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo\Proponente;

use RichiesteBundle\GestoriRichiestePA\ASezioneRichiesta;
use RichiesteBundle\GestoriRichiestePA\IRiepilogoRichiesta;
use PaginaBundle\Services\Pagina;
use Symfony\Component\DependencyInjection\ContainerInterface;
use RichiesteBundle\Entity\Proponente;
use BaseBundle\Exception\SfingeException;
use RichiesteBundle\Ricerche\RicercaSoggettoProponente;


class CercaProponente extends ASezioneRichiesta
{
    const TITOLO = 'Cerca proponente';
    const SOTTOTITOLO = 'Seleziona il proponente';

    const NOME_SEZIONE = 'proponente';

    /**
     * @var Proponente
     */
    protected $proponente;

    /**
     * @var \RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo\Proponente
     */
    protected $parent;

    public function __construct(
        ContainerInterface $container, 
        IRiepilogoRichiesta $riepilogo, 
        ASezioneRichiesta $parent)
    {
        parent::__construct($container, $riepilogo);
        $this->parent = $parent;
        $parent->checkRichiesta($this->richiesta);
    }

    public function getTitolo()
    {
        return self::TITOLO;
    }

    public function valida()
    {
    }

    public function getUrl()
    {
        return $this->generateUrl(self::ROTTA, array(
            'id_richiesta' => $this->richiesta->getId(),
            'nome_sezione' => self::NOME_SEZIONE,
            'parametro1' => 'cerca_proponente',
        ));
    }

    public function visualizzaSezione(array $parametri)
    {
        $this->setupPagina(self::TITOLO, self::SOTTOTITOLO);

		$isRichiestaDisabilitata = $this->container->get("gestore_richieste")->getGestore($this->richiesta->getProcedura())->isRichiestaDisabilitata();

		if ($isRichiestaDisabilitata) {
			throw new SfingeException("Impossibile effettuare questa operazione");
		}

		$soggetto = new RicercaSoggettoProponente();
		$soggetto->richiesta = $this->richiesta;
		$risultato = $this->container->get("ricerca")->ricerca($soggetto);

		$dati = array(
			'soggetti' => $risultato["risultato"], 
			"form" => $risultato["form_ricerca"], 
			"filtro_attivo" => $risultato["filtro_attivo"], 
			"id_richiesta" => $this->richiesta->getId(),
			'url_indietro' => $this->parent->getUrl(),
		);

		$response = $this->render("RichiesteBundle:ProcedurePA:cercaProponente.html.twig", $dati);

		return $response;
	
    }
}