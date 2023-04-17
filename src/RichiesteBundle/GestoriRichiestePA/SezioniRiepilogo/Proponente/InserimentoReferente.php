<?php

namespace RichiesteBundle\GestoriRichiestePA\SezioniRiepilogo\Proponente;

use RichiesteBundle\GestoriRichiestePA\ASezioneRichiesta;
use Symfony\Component\DependencyInjection\ContainerInterface;
use RichiesteBundle\GestoriRichiestePA\IRiepilogoRichiesta;
use RichiesteBundle\Entity\Proponente;
use AnagraficheBundle\Entity\Persona;
use RichiesteBundle\Entity\Referente;
use BaseBundle\Exception\SfingeException;

class InserimentoReferente extends ASezioneRichiesta{

    const TITOLO = 'Associazione referente';
    const SOTTOTITOLO = 'Associare la persona selezionata al tipo di referente';
    const VALIDATION_GROUP = 'dati_progetto';
    
    const NOME_SEZIONE = 'proponente';

    /**
     * @var Proponente
     */
    protected $proponente;
    /**
     * @var ASezioneRichiesta
     */
    protected $parent;
    /**
     * @var Persona
     */
    protected $persona;

    public function __construct(ContainerInterface $container, IRiepilogoRichiesta $riepilogo, ASezioneRichiesta $parent,  $id_referente)
    {
        parent::__construct($container, $riepilogo);
        $this->parent = $parent;
        $this->proponente = $parent->getProponente();
        $this->persona = $this->getEm()->getRepository('AnagraficheBundle:Persona')->findOneById($id_referente);
        if(\is_null($this->persona)){
            throw new SfingeException('Persona non trovato');
        }
        $parent->checkRichiesta($this->proponente->getRichiesta());
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
            'parametro1' => $this->proponente->getId(),
            'parametro2' => 'referente',
            'parametro3' => 'inserisci',
            'parametro4' => $this->referente->getId(),
        ));
    }
   

    public function visualizzaSezione(array $parametri)
    {
        $isRichiestaDisabilitata = $this->getGestoreRichiesta()->isRichiestaDisabilitata();

		if ($isRichiestaDisabilitata) {
			throw new SfingeException("Impossibile effettuare questa operazione");
		}

        $referente = new Referente();
        $referente->setProponente($this->proponente);
        $referente->setPersona($this->persona);


		$form = $this->createForm("RichiesteBundle\Form\ReferenteType", $referente, array(
            "tipi_referenza" => $this->getGestoreProponenti()->getTipiReferenzaAmmessi(),
            "url_indietro" => $this->parent->getUrl(),
        ));
        $form->handleRequest($this->getCurrentRequest());
			if ($form->isSubmitted() && $form->isValid()) {
				$em = $this->getEm();
				try {
					$em->persist($referente);
					$em->flush();
                    $this->addFlash('success', 'Referente aggiunto correttamente');
                    return $this->redirect($this->parent->getUrl());
				} catch (\Exception $e) {
                    $this->container->get('logger')->error($e->getMessage());
                    $this->addError('Referente non aggiunto');
                    throw $e;
				}
		}

		$dati = array(
            "richiesta" => $this->richiesta,
            "proponente" => $this->proponente,
            "persona" => $this->persona,
            "form" => $form->createView()
        );

		return $this->render('RichiesteBundle:ProcedurePA:inserisciReferente.html.twig', $dati);
    }
}