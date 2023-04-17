<?php

namespace RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo\Proponente;

use RichiesteBundle\GestoriRichiestePA\ASezioneRichiesta;
use Symfony\Component\DependencyInjection\ContainerInterface;
use RichiesteBundle\GestoriRichiestePA\IRiepilogoRichiesta;
use RichiesteBundle\Entity\Proponente;
use BaseBundle\Exception\SfingeException;
use RichiesteBundle\Entity\Referente;

class DettaglioReferente extends ASezioneRichiesta{
    const TITOLO = 'Dettaglio referente';
    const SOTTOTITOLO = 'dettaglio di un referente associato ad un proponente';

    const NOME_SEZIONE = 'referente';
    
    /**
     * @var ASezioneRichiesta
     */
    protected $parent;

    /**
     * @var Proponente
     */
    protected $proponente;

    /**
     * @var Referente
     */
    protected $referente;

    /**
     * @param ContainerInterface $container
     * @param IRiepilogoRichiesta $riepilogo
     * @param \RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo\Proponente $parent
     * @param integer $id_proponente
     * 
     * @throws SfingeException
     */
    public function __construct(ContainerInterface $container, IRiepilogoRichiesta $riepilogo, ASezioneRichiesta $parent,$id_referente)
    {
        parent::__construct($container, $riepilogo);
        $this->parent = $parent;
        $this->referente = $this->getEm()->getRepository('RichiesteBundle:Referente')->findOneById($id_referente);
        if( \is_null($this->referente)){
            throw new SfingeException('Referente non trovato');
        }
        $this->proponente = $this->referente->getProponente();
        $parent->checkRichiesta($this->proponente->getRichiesta());
    }

    public function getTitolo()
    {
     return self::TITOLO;
    }

    public function getUrl()
    {
        return $this->generateUrl(self::ROTTA, array(
            'id_richiesta' => $this->richiesta->getId(),
            'nome_sezione' => \RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo\Proponente::NOME_SEZIONE,
            'parametro1' => $this->proponente->getId(),
            'parametro2' => self::NOME_SEZIONE,
            'parametro3' => $this->referente->getId(),
        ));
    }

    public function valida()
    {
    }

    public function visualizzaSezione(array $parametri)
    {
        $this->setupPagina(self::TITOLO, self::SOTTOTITOLO);
        return $this->render("RichiesteBundle:ProcedurePA:dettaglioReferente.html.twig", array(
            "referente" => $this->referente,
            "richiesta" => $this->richiesta,
            "proponente" => $this->proponente,
            'indietro' => $this->parent->getUrl(),
        ));
    }
}