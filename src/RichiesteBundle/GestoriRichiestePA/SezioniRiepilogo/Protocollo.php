<?php

namespace RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo;

use RichiesteBundle\GestoriRichiestePA\ASezioneRichiesta;
use ProtocollazioneBundle\Entity\RichiestaProtocolloFinanziamento;
use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\GestoriRichiestePA\IRiepilogoRichiesta;
use ProtocollazioneBundle\Entity\RichiestaProtocollo;
use BaseBundle\Exception\SfingeException;
use Symfony\Component\Validator\Constraints\Valid;

class Protocollo extends ASezioneRichiesta{
    const TITOLO = 'Protocollo';
    const SOTTOTITOLO = "mostra l'elenco dei proponenti per la richiesta";
    const NOME_SEZIONE = 'protocollo';
    const VALIDATOR = 'legge14';

    /**
     * {@inheritDoc}
     */
    public function getTitolo()
    {
     return self::TITOLO;
    }

    /**
     * @var bool
     */
    protected $checkPresenzaProtocollo = true;

    public function valida()
    {
        $protocollo = $this->richiesta->getRichiesteProtocollo()->first();
        if(!$protocollo){
            $this->listaMessaggi[] = 'Compilare i campi della sezione';
            return;
        }
        if($this->checkPresenzaProtocollo && $this->isProtocolloPresente($protocollo)){
            $this->listaMessaggi[] = 'Il protocollo inserito è già presente a sistema';
            return;
        }
        
        if($this->checkPresenzaProtocollo && !$this->isProtocolloCompleto($protocollo)){
            $this->listaMessaggi[] = 'Il protocollo inserito non è completo';
            return;
        } 
        
        $validator = $this->container->get('validator');
        /** @var \Symfony\Component\Validator\ConstraintViolationListInterface $validationList */
        $validationList = $this->validator->validate($protocollo, new Valid(array('traverse'=> array(self::VALIDATOR))));
        $this->listaMessaggi = array();
        /** @var \Symfony\Component\Validator\ConstraintViolationInterface $validationElement */
        foreach($validationList as $validationElement ){    
            $this->listaMessaggi[] = $validationElement->getMessage();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getUrl()
    {
        return $this->generateUrl(self::ROTTA, array(
            'id_richiesta' => $this->richiesta->getId(),
            'nome_sezione' => self::NOME_SEZIONE,
        ));
    }
   
    /**
     * {@inheritDoc}
     */
    public function visualizzaSezione(array $parametri)
    {
        $this->setupPagina(self::TITOLO, self::SOTTOTITOLO);

        $protocollo = $this->richiesta->getRichiesteProtocollo()->first();
        if(!$protocollo){
            $protocollo = $this->initProtocollo($this->richiesta);
        }

        $form = $this->createForm('RichiesteBundle\Form\Bando60\DatiProtocolloType', $protocollo, array(
            'url_indietro' => $this->generateUrl(IRiepilogoRichiesta::ROTTA,array('id_richiesta' => $this->richiesta->getId())),
            "disabled" => $this->container->get('gestore_richieste')
                ->getGestore($this->richiesta->getProcedura())->isRichiestaDisabilitata($this->richiesta->getId()),
        ));
        $form->handleRequest( $this->getCurrentRequest());
        if($form->isSubmitted() && $form->isValid()){
            try{
                if($this->checkPresenzaProtocollo && $this->isProtocolloPresente($protocollo)){
                    throw new SfingeException('Il protocollo inserito è già presente a sistema');
                }
                $em = $this->getEm();
                $em->persist($protocollo);
                $em->persist($this->richiesta);
                $em->flush();
                $this->addFlash('success','Informazioni salvate correttamente');
            }
            catch(SfingeException $e){
                $this->container->get('logger')->error($e->getMessage());
                $this->addError($e->getMessage());
            }
            catch( \Exception $e ){
                $this->container->get('logger')->error($e->getMessage());
                $this->addError('Errore durante il salvataggio delle informazioni');
            }
        }
        return $this->render('RichiesteBundle:Bando60:datiProtocollo.html.twig', array('form' => $form->createView()));
    }

    /**
     * @param Richiesta &$richiesta
     * @return RichiestaProtocolloFinanziamento
     */
    protected function initProtocollo(Richiesta &$richiesta){
        $protocollo = new RichiestaProtocolloFinanziamento();
        $protocollo->setRichiesta($this->richiesta);
        $protocollo->setProcedura($this->richiesta->getProcedura());
        $protocollo->setDataPg($richiesta->getDataCreazione());
        $protocollo->setStato(RichiestaProtocollo::POST_PROTOCOLLAZIONE);
        $protocollo->setFase(0);
        $protocollo->setEsitoFase(1);
        $richiesta->addRichiesteProtocollo($protocollo);
        return $protocollo;
    }

    /**
     * @throws SfingeException
     * @param RichiestaProtocollo $protocollo
     * @return boolean
     */
    protected function isProtocolloPresente(RichiestaProtocollo $protocollo){
        return $this->getEm()
            ->getRepository('ProtocollazioneBundle:RichiestaProtocolloFinanziamento')
            ->hasProtocolloSimile($protocollo);
    }
    
    protected function isProtocolloCompleto(RichiestaProtocollo $protocollo){
        if(is_null($protocollo->getNumPg())) {
            return false;
        }
         return true;
    }

    /**
     * @param bool $verifica
     * @return self
     */
    public function verificaPresenzaProtocollo($verifica){
        $this->checkPresenzaProtocollo = $verifica;

        return $this;
    }
}