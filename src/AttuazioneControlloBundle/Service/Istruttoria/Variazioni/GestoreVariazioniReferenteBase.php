<?php

namespace AttuazioneControlloBundle\Service\Istruttoria\Variazioni;

use AttuazioneControlloBundle\Entity\VariazioneReferente;
use AttuazioneControlloBundle\Entity\VariazioneSingoloReferente;
use AttuazioneControlloBundle\Service\Istruttoria\AGestoreVariazioni;
use RichiesteBundle\Entity\Proponente;
use RichiesteBundle\Entity\Referente;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class GestoreVariazioniReferenteBase extends AGestoreVariazioni implements IGestoreVariazioniReferente {
    /**
     * @var VariazioneReferente
     */
    protected $variazione;

    public function __construct(VariazioneReferente $variazione, ContainerInterface $container) {
        $this->variazione = $variazione;
        $this->container = $container;
    }

    protected function applicaVariazione(): void {
        foreach ($this->variazione->getVariazioniSingoloReferente() as $variazioneSingola) {
            $this->applicaSingolaVariazione($variazioneSingola);
        }
    }

    private function applicaSingolaVariazione(VariazioneSingoloReferente $variazione): void {
        $referente = $variazione->getReferenza();
        $proponente = $referente->getProponente();

        $nuovoReferente = new Referente();
        $nuovoReferente->setProponente($proponente);
        $nuovoReferente->setTipoReferenza($referente->getTipoReferenza());
        $nuovoReferente->setPersona($variazione->getPersona());
        $nuovoReferente->setEmailPec($variazione->getEmailPec());
        $nuovoReferente->setQualifica($variazione->getQualifica());
        $nuovoReferente->setRuolo($variazione->getRuolo());

        $proponente->removeReferenti($referente);
        $this->getEm()->remove($referente);

        $proponente->addReferenti($nuovoReferente);
        $this->getEm()->persist($nuovoReferente);
    }

    public function dettaglioReferente(Proponente $proponente): Response {
        $opzioni = $this->container->getParameter('opzioni_referente');
        $procedura = $this->variazione->getRichiesta()->getProcedura();
        $opzioniProcedura = $opzioni[$procedura->getId()] ?? [];
        return $this->render('AttuazioneControlloBundle:Istruttoria\Variazioni:dettaglio_referente.html.twig', [
            'variazione' => $this->variazione,
            'proponente' => $proponente,
            'opzioni' => $opzioniProcedura,
        ]);
    }
}
