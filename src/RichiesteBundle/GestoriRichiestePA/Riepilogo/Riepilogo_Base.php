<?php

namespace RichiesteBundle\GestoriRichiestePA\Riepilogo;

use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\GestoriRichiestePA\IRiepilogoRichiesta;
use RichiesteBundle\GestoriRichiestePA\ISezioneRichiesta;
use RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo\DatiProgetto;
use Symfony\Component\DependencyInjection\ContainerInterface;
use PaginaBundle\Services\Pagina;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use RichiesteBundle\GestoriRichiestePA\Azioni\Visualizza;
use RichiesteBundle\GestoriRichiestePA\Azione;
use BaseBundle\Entity\StatoRichiesta;
use RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo\PianoCosto;
use RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo\Protocollo;
use RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo\Documenti;
use RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo\Proponente;
use RichiesteBundle\GestoriRichiestePA\Azioni\PassaInIstruttoria;
use RichiesteBundle\Service\IGestoreRichiesta;
use Symfony\Component\HttpFoundation\Response;
use BaseBundle\Exception\SfingeException;

class Riepilogo_Base implements IRiepilogoRichiesta
{
    /**
     * @var Richiesta
     */
    protected $richiesta;

    /**
     * @var ISezioneRichiesta[]
     */
    protected $sezioni;

    /**
     * @var Azione[]
     */
    protected $azioni = array();    

    /**
     * @var array Elenco di messaggi di validazione a livello Richiesta
     */
    protected $messaggi;

    /**
     * @var ContainerInterface $container
     */
    protected $container;

    public function __construct(ContainerInterface $container, Richiesta $richiesta){
        $this->container = $container;
        $this->richiesta = $richiesta;
        $this->inizializzaAzioni();
        $this->inizializzaSezioni();  
    }

    public function inizializzaAzioni(){
        $this
            ->addAzione(new Visualizza($this->container->get('router'), $this))
            ->addAzione(new PassaInIstruttoria($this->container, $this));
    }

    public function inizializzaSezioni()
    {
        $this
            ->addSezione(new DatiProgetto($this->container, $this))
            ->addSezione(new Proponente($this->container, $this))
            ->addSezione(new PianoCosto($this->container, $this))
            ->addSezione(new Protocollo($this->container, $this))
            ->addSezione(new Documenti($this->container, $this));
    }

    public function getBarraAvanzamento(){
        $statoRichiesta = $this->richiesta->getStato()->getCodice();
        
        $arrayStati = array(
            'Inserita' => true, 
            'Completata' => $statoRichiesta == StatoRichiesta::PRE_PROTOCOLLATA,
        );

		return $arrayStati;
    }

    public function getSezioni(){
        return $this->sezioni;
    }

    /**
     * @param string $name Identificativo della sezione
     * @param ISezioneRichiesta $sezione
     * @return self
     */
    public function addSezione(ISezioneRichiesta $sezione){
        $this->sezioni[] = $sezione;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isValido(){
        $this->validaRichiesta();

        foreach( $this->sezioni as $sezione ){ 
            if(!$sezione->isValido()){
                return false;
            }
        }

        return true;
    }

    /**
     * Effettua la validazione della richiesta e delle sue sezioni
     */
    public function validaRichiesta(){
        foreach ($this->sezioni as $sezione) {
            $sezione->valida();
        }
    }

    /**
     * @var array
     */
    public function getMessaggi()
    {
        return $this->messaggi;
    }

    /**
     * @return array
     */
    public function getVociMenu()
    {
        $res = array();
        foreach ($this->azioni as $azione) {
            if ($azione->isVisibile()) {
				$a = $azione->toVoceMenu();
                $res[] = $a;
            }
        }

        return $res;
    }

    /**
     * @return Richiesta
     */
    public function getRichiesta()
    {
        return $this->richiesta;
    }

    public function visualizzaSezione($nome_sezione, array $parametri){
        $pagina = $this->container->get('pagina');  /** @var Pagina $pagina */
        $pagina->aggiungiElementoBreadcrumb('Elenco progetti', $this->generateUrl('procedura_pa_elenco'));
        $pagina->aggiungiElementoBreadcrumb('Riepilogo progetto', 
            $this->generateUrl('procedura_pa_dettaglio_richiesta', array('id_richiesta' => $this->richiesta->getId()))
        );
        
        $sezioni = \array_values(\array_filter($this->sezioni, function($sezione) use($nome_sezione, $parametri){
			if($nome_sezione != $sezione::NOME_SEZIONE)
				return false;
			if($nome_sezione == 'piano_costo' && (($parametri[0] ?? 0) != $sezione->getProponente()->getId())){
				return false;
			}
			return true;
		}));
		return $sezioni[0]->visualizzaSezione($parametri);
    }

    public function generateUrl($name, array $params = array()){
        $router = $this->container->get('router');
        return $router->generate($name, $params, UrlGeneratorInterface::ABSOLUTE_PATH);
    }

    /**
     * @return Azione[]
     */
    public function getAzioni(){
        return $this->azioni;
    }

    /**
     * @return boolean
     */
    public function isRichiestaDisabilitata(){
        $disabilitata = !$this->isGranted('ROLE_GESTIONE_PROCEDURA_PA');
        return $disabilitata || $this->getGestoreRichiesta()->isRichiestaDisabilitata($this->richiesta->getId());
    }

    /**
     * @return IGestoreRichiesta
     */
    protected function getGestoreRichiesta(){
        return $this->container->get('gestore_richieste')->getGestore($this->richiesta->getProcedura());
    }

    public function getUrl()
    {
        return $this->generateUrl('procedura_pa_dettaglio_richiesta', array('id_richiesta' => $this->richiesta->getId()));
    }

    /**
     * @param Azione $azione
     * @return self
     */
    public function addAzione(Azione $azione){
        $this->azioni[$azione->getNomeAzione()] = $azione;
        return $this;
    }

     /**
     * @param string $nome_azione Nome indentificativo dell'azione da richiamare
     * @throws SfingeException
     * @return Response
     */ 
    public function risultatoAzione($nome_azione){
        if(\array_key_exists($nome_azione, $this->azioni)){
            return $this->azioni[$nome_azione]->getRisultatoEsecuzione();
        }
        return new SfingeException('Azione non trovata');
    }

    /**
     * {@inheritDoc}
     */
    public function getContainer(){
        return $this->container;
    }

    protected function isGranted($attributes, $object = null) {
		if (!$this->container->has('security.authorization_checker')) {
			throw new \LogicException('The SecurityBundle is not registered in your application.');
		}

		return $this->container->get('security.authorization_checker')->isGranted($attributes, $object);
	}
}
