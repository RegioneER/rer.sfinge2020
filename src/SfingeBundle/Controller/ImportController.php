<?php

namespace SfingeBundle\Controller;

use BaseBundle\Controller\BaseController;
use BaseBundle\Entity\Indirizzo;
use DocumentoBundle\Entity\DocumentoFile;
use DocumentoBundle\Entity\TipologiaDocumento;
use DocumentoBundle\Form\Type\DocumentoFileType;
use ProtocollazioneBundle\Entity\RichiestaProtocollo;
use ProtocollazioneBundle\Entity\RichiestaProtocolloFinanziamento;
use RichiesteBundle\Entity\OggettoRichiesta;
use RichiesteBundle\Entity\PianoCosto;
use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\Entity\VocePianoCosto;
use SoggettoBundle\Entity\IncaricoPersona;
use SoggettoBundle\Entity\TipoIncarico;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PaginaBundle\Annotations\PaginaInfo;
use PaginaBundle\Annotations\Menuitem;

use SoggettoBundle\Entity\SoggettoRepository;
//use SoggettoBundle\Form\Entity\Soggetto;

use AnagraficheBundle\Entity\Persona;
use SoggettoBundle\Entity\Soggetto;
use SoggettoBundle\Entity\Azienda;
use AnagraficheBundle\Form\Entity\RicercaPersone;
use RichiesteBundle\Entity\Proponente;


class ImportController extends BaseController
{
	
	/**
     * Route("/import_383", name="import_383")
     */
    public function import383Action()
    {
        return $this->get("gestore_importazione_procedura_383")->importaRichieste();
    }
	
	/**
     * Route("/import_383_OR", name="import_obiettivi_realizzativi_383")
     */
    public function importOR383Action()
    {
        return $this->get("gestore_importazione_procedura_383")->importaOr383();
    }
	
    /**
     * Route("/import_380", name="import_380")
     */
    public function import380Action()
    {
        return $this->get("gestore_importazione_procedura_380")->importaRichieste();
    }
	
	/**
     * Route("/import_380_OR", name="import_obiettivi_realizzativi_380")
     */
    public function importOR380Action()
    {
        return $this->get("gestore_importazione_procedura_380")->importaOr380();
    }
    
    /**
     * Route("/import_373", name="import_373")
     */
    public function import373Action()
    {
        return $this->get("gestore_importazione_procedura_373")->importaRichieste();
    } 
    
    /**
     * Route("/import_360", name="import_360")
     */
    public function import360Action()
    {
        return $this->get("gestore_importazione_procedura_360")->importaRichieste();
    }  
	
	/**
     * Route("/import_373_nature", name="import_nature_373")
     */
    public function importNature373Action()
    {
        return $this->get("gestore_importazione_procedura_373")->importaNaturelab373();
    }
	
	/**
     * Route("/import_373_OR", name="import_obiettivi_realizzativi_373")
     */
    public function importOR373Action()
    {
        return $this->get("gestore_importazione_procedura_373")->importaOr373();
    }

	/**
     * Route("/import_373_laboratori_organismi", name="import_laboratori_organismi_373")
     */
    public function importLaboratoriOrganismi373Action()
    {
        return $this->get("gestore_importazione_procedura_373")->importaLaboratoriOrganismi373();
    }	
	
	/**
     * @Route("/import_variazioni_373", name="import_variazioni_373")
	 * @PaginaInfo(titolo="Importa Variazione 373", sottoTitolo="Importazione delle variazioni per il bando 373")
	 * @Menuitem(menuAttivo = "importaVariazioni373")
	 * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="Home", route="home"), @ElementoBreadcrumb(testo="Importa Variazione 373")})
     */
    public function importaVariazioni373Action()
    {
        return $this->get("gestore_importazione_procedura_373")->importaVariazioni373();
    }
	
}