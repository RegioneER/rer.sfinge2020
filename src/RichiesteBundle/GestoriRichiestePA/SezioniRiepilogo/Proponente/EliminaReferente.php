<?php

namespace RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo\Proponente;

use RichiesteBundle\GestoriRichiestePA\ASezioneRichiesta;
use Symfony\Component\DependencyInjection\ContainerInterface;
use RichiesteBundle\GestoriRichiestePA\IRiepilogoRichiesta;
use RichiesteBundle\Entity\Proponente;
use BaseBundle\Exception\SfingeException;
use RichiesteBundle\Entity\Referente;


class EliminaReferente extends ASezioneRichiesta
{

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
    public function __construct(ContainerInterface $container, IRiepilogoRichiesta $riepilogo, ASezioneRichiesta $parent,  $id_referente)
    {
        parent::__construct($container, $riepilogo);
        $this->parent = $parent;
        $this->referente = $this->getEm()->getRepository('RichiesteBundle:Referente')->findOneById($id_referente);
        $this->checkReferente();
    }

    public function checkReferente(){
        if( \is_null($this->referente)){
            throw new SfingeException('Referente non trovato');
        }
        $this->proponente = $this->referente->getProponente();
        if( \is_null($this->proponente)){
            throw new SfingeException('Proponente non trovato');
        }
        if($this->referente->getProponente() != $this->proponente){
            throw new SfingeException('Tentato accesso al referente non autorizzato');
        }
        $this->parent->checkRichiesta($this->proponente->getRichiesta());
    }

    public function getTitolo()
    {
        return '';
    }

    public function valida()
    {

    }

    public function getUrl()
    {
        return $this->parent->getUrl();
    }

    public function visualizzaSezione(array $parametri)
    {
        try{
            if($this->riepilogo->isRichiestaDisabilitata()){
                throw new SfingeException('Impossibile eliminare elemento');
            }
            $this->container->get("base")->checkCsrf('token', '_token');
            $this->getEm()->remove($this->referente);
            $this->getEm()->flush();
            $this->addFlash('success', 'Referente eliminato con successo');
        }
        catch( \Exception $e ){
            $this->container->get('logger')->error($e, array('referente_id' => $this->referente));
            $this->addError("Errore durante l'eliminazione del referente");
        }
        return $this->redirect($this->parent->getUrl());
    }
}