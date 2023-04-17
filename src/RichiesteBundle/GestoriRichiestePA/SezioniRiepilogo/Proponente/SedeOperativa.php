<?php

namespace RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo\Proponente;

use RichiesteBundle\GestoriRichiestePA\ASezioneRichiesta;
use Symfony\Component\DependencyInjection\ContainerInterface;
use RichiesteBundle\GestoriRichiestePA\IRiepilogoRichiesta;
use RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo\Proponente as SezioneProponente;
use RichiesteBundle\Entity\Proponente;
use Doctrine\Common\Collections\ArrayCollection;
use RichiesteBundle\Entity\SedeOperativa as ProponenteSedeOperativa;
use RichiesteBundle\Form\ProponenteSedeOperativaType;
use Symfony\Component\HttpFoundation\Response;

class SedeOperativa extends ASezioneRichiesta{

    const TITOLO = 'Sedi operative';
    const SOTTOTITOLO = 'Selezionare quali sedi sono sedi operative del progetto';
    const VALIDATION_GROUP = 'dati_progetto';
    
    const NOME_SEZIONE = 'sede_operativa';

    /**
     * @var SezioneProponente
     */
    protected $parent;

    /**
     * @var Proponente
     */
    protected $proponente;

    public function __construct(ContainerInterface $container, IRiepilogoRichiesta $riepilogo, ASezioneRichiesta $parent)
    {
        parent::__construct($container, $riepilogo);
        $this->proponente = $parent->getProponente();
        $this->parent = $parent;
        $parent->checkRichiesta($this->proponente->getRichiesta());   
    }

    public function getTitolo()
    {
     return self::TITOLO;
    }

    public function getUrl(): string
    {
        return $this->generateUrl(self::ROTTA, array(
            'id_richiesta' => $this->richiesta->getId(),
            'nome_sezione' => \RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo\Proponente::NOME_SEZIONE,
            'parametro1' => $this->proponente->getId(),
            'parametro2' => self::NOME_SEZIONE,
        ));
    }

    public function valida()
    {
        if(!$this->proponente->getSedeLegaleComeOperativa() && 0 == \count($this->proponente->getSedi())){
            
            $this->listaMessaggi =  'Selezionare una sede operativa';

        }
    }

    public function visualizzaSezione(array $parametri)
    {
        $this->setupPagina(self::TITOLO, self::SOTTOTITOLO);
        $richiestaDisabilitata = $this->riepilogo->isRichiestaDisabilitata();
        if( \count($this->proponente->getSedi()) == 0 ){
            $nuovaSede = new ProponenteSedeOperativa();
            $nuovaSede->setProponente($this->proponente);
            $this->proponente->addSedi($nuovaSede);
        }

        $form = $this->createForm(ProponenteSedeOperativaType::class, $this->proponente, array(
            'url_indietro' => $this->parent->getUrl(),
            'disabled' => $richiestaDisabilitata,
        ));

        $form->handleRequest($this->getCurrentRequest());
        if($form->isSubmitted() && $form->isValid()){
            $em = $this->getEm();
            $connection = $em->getConnection(); /** @var \Doctrine\DBAL\Connection $connection */
            try{
                $connection->beginTransaction();
                foreach($this->proponente->getSedi() as $sede){
                    $sede->setProponente($this->proponente);
                    $em->persist($sede);
                }
        
                $em->persist($this->proponente);
                $em->flush();
        
                $connection->commit();
                $this->addFlash('success','Operazione effettuata con successo');
            }
            catch(\Exception $e){
                if($connection->isTransactionActive()){
                    $connection->rollBack();
                }
                $this->container->get('logger')->error($e->getMessage());
                $this->addError('Errore durante il salvataggio dei dati');
            }
        }

        return $this->render('RichiesteBundle:ProcedurePA:sediOperative.html.twig', array(
            'form' => $form->createView(),
            'proponente' => $this->proponente,
            'this_url' => $this->getUrl(),
        ));
    }

}