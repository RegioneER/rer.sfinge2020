<?php

namespace RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo\Proponente;

use RichiesteBundle\GestoriRichiestePA\ASezioneRichiesta;
use RichiesteBundle\GestoriRichiestePA\IRiepilogoRichiesta;
use Symfony\Component\DependencyInjection\ContainerInterface;
use RichiesteBundle\Entity\Proponente;
use BaseBundle\Exception\SfingeException;
use Symfony\Component\HttpFoundation\RedirectResponse;


class InserisciProponente extends ASezioneRichiesta
{
    const TITOLO = 'Associa proponente';
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
            'parametro1' => 'aggiungi_proponente',
			'parametro2' => 'id_soggetto',
            
        ));
    }

    public function visualizzaSezione(array $parametri)
    {
        
		$id_soggetto= \array_shift($parametri);
		$this->setupPagina(self::TITOLO, self::SOTTOTITOLO);

		$richiesta = $this->richiesta;
		if (\is_null($richiesta)) {
			throw new SfingeException("Richiesta indicata non esiste");
		}
		$isRichiestaDisabilitata = $this->container->get("gestore_richieste")->getGestore($richiesta->getProcedura())->isRichiestaDisabilitata();

		if ($isRichiestaDisabilitata) {
			throw new SfingeException("Impossibile effettuare questa operazione");
		}

		$soggetto = $this->getEm()->getRepository("SoggettoBundle:Soggetto")->find($id_soggetto);
		if (\is_null($soggetto)) {
			throw new SfingeException("Il soggetto indicato non esiste");
		}
		
		$nProponenti = $richiesta->getProponenti()->count();
		if ($nProponenti >= $richiesta->getProcedura()->getNumeroProponenti()) {
			throw new SfingeException("Impossibile aggiungere un ulteriore proponente");
		}

		$proponentePresente = $richiesta->getProponenti()->filter(function(Proponente $p) use($soggetto){
			return $p->getSoggetto() == $soggetto;
		})->count() > 0;

		if($proponentePresente){
			throw new SfingeException("Il soggetto indicato è già presente nella richiesta");
		}

		$proponente = new Proponente();
		$proponente->setSoggetto($soggetto);
		$proponente->setRichiesta($richiesta);

		$this->getEm()->persist($proponente);
		$this->getEm()->flush();
		$this->addFlash('success', 'Proponente aggiunto correttamente');
		
		return new RedirectResponse($this->parent->getUrl());
	
    }
}