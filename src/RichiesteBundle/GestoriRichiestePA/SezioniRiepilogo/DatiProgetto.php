<?php

namespace RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo;

use RichiesteBundle\GestoriRichiestePA\ASezioneRichiesta;
use RichiesteBundle\GestoriRichiestePA\IRiepilogoRichiesta;
use PaginaBundle\Services\Pagina;
use Symfony\Component\Validator\Constraints\Valid;

class DatiProgetto extends ASezioneRichiesta
{
    const TITOLO = 'Gestione dati progetto';
    const SOTTOTITOLO = 'Inserire titolo e abstract';
    const VALIDATION_GROUP = 'dati_progetto';
    
    const NOME_SEZIONE = 'dati_progetto';

    protected $validations_groups = array(); 

    public function getTitolo()
    {
     return self::TITOLO;
    }

    public function valida()
    {
        /** @var \Symfony\Component\Validator\ConstraintViolationListInterface $validationList */
        $v = $this->getValidationsGroups();
        $validationList = $this->validator->validate($this->richiesta,  new Valid(), $this->getValidationsGroups()); 
        $this->listaMessaggi = array();
        foreach($validationList as $validationElement ){    /** @var \Symfony\Component\Validator\ConstraintViolationInterface $validationElement */
            $this->listaMessaggi[] = $validationElement->getMessage();
        }
    }

    /**
     * @return string[]
     */
    public function getValidationsGroups(){
        return \count($this->validations_groups) ? $this->validations_groups : array(self::VALIDATION_GROUP);
    }
    /**
     * @param string[] $validazioni
     */
    public function setValidationsGroups(array $validazioni){
        $this->validations_groups = $validazioni;
    }

    public function getUrl()
    {
        return $this->generateUrl(self::ROTTA, array(
            'id_richiesta' => $this->richiesta->getId(),
            'nome_sezione' => self::NOME_SEZIONE,
        ));
    }
   

    public function visualizzaSezione(array $parametri)
    {
        $this->setupPagina(self::TITOLO, self::SOTTOTITOLO);
        $form = $this->createForm('RichiesteBundle\Form\Bando60\DatiProgettoType', $this->richiesta, array(
            'url_indietro' => $this->generateUrl(IRiepilogoRichiesta::ROTTA,array('id_richiesta' => $this->richiesta->getId())),
            'disabled' => $this->riepilogo->isRichiestaDisabilitata(),
            'validation_groups' => $this->getValidationsGroups(),
        ));
        $form->handleRequest( $this->getCurrentRequest());
        if($form->isSubmitted() && $form->isValid()){
            try{
                $em = $this->getEm();
                $em->persist($this->richiesta);
                $em->flush();
                $this->addFlash('success','Informazioni salvate correttamente');
            }
            catch( \Exception $e ){
                $this->container->get('logger')->error($e->getMessage());
                $this->addError('Errore durante il salvataggio delle informazioni');
            }
        }
        return $this->render('RichiesteBundle:Richieste:datiProgetto.html.twig', array('form' => $form->createView()));
    }

}
