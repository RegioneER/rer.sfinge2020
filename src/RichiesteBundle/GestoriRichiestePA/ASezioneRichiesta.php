<?php

namespace RichiesteBundle\GestoriRichiestePA;

use Symfony\Component\DependencyInjection\ContainerInterface;
use RichiesteBundle\Entity\Richiesta;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use BaseBundle\Service\BaseService;
use RichiesteBundle\Utility\EsitoValidazione;
use SfingeBundle\Entity\Procedura;
use SfingeBundle\Entity\ProceduraPA;
use BaseBundle\Exception\SfingeException;
use RichiesteBundle\Service\IGestoreFaseProcedurale;
use RichiesteBundle\Service\IGestoreRichiesta;
use RichiesteBundle\Service\IGestoreProponenti;
use IstruttorieBundle\Service\IGestoreIstruttoria;
use RichiesteBundle\Service\IGestorePianoCosto;

abstract class ASezioneRichiesta extends BaseService implements ISezioneRichiesta {
    const ROTTA = 'procedura_pa_sezione';

    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var ValidatorInterface
     */
    protected $validator;
    /**
     * @var Richiesta
     */
    protected $richiesta;

    /**
     * @var IRiepilogoRichiesta
     */
    protected $riepilogo;

    /**
     * @var string[]
     */
    protected $listaMessaggi = [];

    public function __construct(ContainerInterface $container, IRiepilogoRichiesta $riepilogo) {
        parent::__construct($container);
        $this->validator = $container->get('validator');
        $this->riepilogo = $riepilogo;
        $this->richiesta = $riepilogo->getRichiesta();
    }

    abstract public function getUrl();

    abstract public function getTitolo();

    public function isValido() {
        return false == \count($this->listaMessaggi);
    }

    public function getMessaggi() {
        return $this->listaMessaggi;
    }

    protected function setupPagina($titolo, $sottotitolo) {
        $pagina = $this->container->get('pagina');  /* @var Pagina $pagina */
        $pagina->setTitolo($titolo);
        $pagina->setSottoTitolo($sottotitolo);
        $pagina->aggiungiElementoBreadcrumb($titolo, $this->getUrl());
    }

    protected function getGestoreRichiesta(): IGestoreRichiesta {
        return $this->container->get('gestore_richieste')->getGestore($this->richiesta->getProcedura());
    }

    protected function getGestoreProponenti():IGestoreProponenti {
        return $this->container->get('gestore_proponenti')->getGestore($this->richiesta->getProcedura());
    }

    protected function getGestoreIstruttoria():IGestoreIstruttoria {
        return $this->container->get('gestore_istruttoria')->getGestore($this->richiesta->getProcedura());
    }

    
    protected function getGestorePianoCosto():IGestorePianoCosto {
        return $this->container->get('gestore_piano_costo')->getGestore($this->richiesta->getProcedura());
    }

    /**
     * @return IGestoreFaseProcedurale
     */
    protected function getGestoreFaseProcedurale():IGestoreFaseProcedurale {
        return $this->container->get('gestore_fase_procedurale')->getGestore($this->richiesta->getProcedura());
    }

    /**
     * @return EsitoValidazione
     */
    protected function getEsito() {
        $esito = new EsitoValidazione(false == \count($this->listaMessaggi));
        $esito->setMessaggiSezione($this->listaMessaggi);
        return $esito;
    }

    public function checkProcedura(Procedura $richiesta) {
        if (!($richiesta->getProcedura() instanceof ProceduraPA)) {
            throw new SfingeException('Accesso alla richiesta non autorizzato');
        }
    }
    public function isRichiestaDisabilitata(){
        return $this->getGestoreRichiesta()->isRichiestaDisabilitata($this->richiesta->getId());
    }

    public function getGestoreModalitaFinanziamento()
    {
        return $this->container->get('gestore_modalita_finanziamento')->getGestore($this->richiesta->getProcedura());
    }
}
