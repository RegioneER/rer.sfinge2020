<?php

namespace IstruttorieBundle\Manager;

use Doctrine\ORM\EntityManager;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use SfingeBundle\Entity\Utente;
use SoggettoBundle\Entity\Soggetto;

class ComunicazioniManager
{
    /**
     * @var EntityManager
     */
    private $manager;

    /**
     * AttivitaGiornataCalendarioManager constructor.
     *
     * @param EntityManager $manager
     */
    public function __construct(EntityManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param SlidingPagination $slidingPagination
     * @return SlidingPagination
     */
    public function aggiornaSlidingPaginationElementiVisibili(SlidingPagination $slidingPagination, Utente $utente, Soggetto $soggetto, $tipo)
    {
        $slidingPaginationRetVal = clone $slidingPagination;
        $incarichiRichieste = [];
        $incarichiAttivi = $this->getIncarichiSuSoggetto($soggetto, $utente);
        $rep = null;
        switch ($tipo) {
            case 'ESITOPRG':
                $rep = $this->manager->getRepository("IstruttorieBundle\Entity\ComunicazioneEsitoIstruttoria");
            break;
            case 'COMATC':
                $rep = $this->manager->getRepository("AttuazioneControlloBundle\Entity\ComunicazioneAttuazione");
            break;
            case 'GENPRG':
            case 'ESITOVAR':
                $rep = $this->manager->getRepository("IstruttorieBundle\Entity\ComunicazioneProgetto");
            break;
        }

        foreach ($slidingPagination as $item) {
            $comunicazione = $rep->find($item->getId());
            $richiestaIn = $comunicazione->getRichiesta();
            $incarichiRichiesteArray = $this->manager->getRepository('SoggettoBundle:IncaricoPersonaRichiesta')->getRichiesteIncaricato($richiestaIn, $utente->getPersona());
            foreach ($incarichiRichiesteArray as $inc) {
                $incarichiRichieste[] = $inc;
            }
        }
        foreach ($incarichiAttivi as $incarico) {
            if (in_array($incarico->getTipoIncarico()->getCodice(), ['UTENTE_PRINCIPALE', 'OPERATORE', 'CONSULENTE', 'LR', 'DELEGATO'])) {
                return $slidingPagination;
            } elseif (in_array($incarico->getTipoIncarico()->getCodice(), ['OPERATORE_RICHIESTA'])) {
                foreach ($slidingPagination as $key => $item) {
                    $comunicazione = $rep->find($item->getId());
                    $richiesta = $comunicazione->getRichiesta();
                    if (!in_array($richiesta->getId(), $incarichiRichieste)) {                    
                        $slidingPaginationRetVal->offsetUnset($key);                     
                        $numItem = $slidingPaginationRetVal->getTotalItemCount();
                        $slidingPaginationRetVal->setTotalItemCount($numItem - 1);
                    }
                }
            }
        }
        return $slidingPaginationRetVal;
    }
    
    /**
     * @return IncaricoPersona[]
     */
    protected function getIncaricoDaSoggetto(Soggetto $soggetto): array {
        $incarichiArray = [];
        $incarichi = $soggetto->getIncarichiPersone();
        foreach ($incarichi as $incarico) {
            if (true == $incarico->isAttivo()) {
                $incarichiArray[] = $incarico;
            }
        }
        return $incarichiArray;
    }

    /**
     * @return IncaricoPersona[]
     */
    protected function getIncaricoPersona(Utente $utente): array {
        $incarichiArray = [];
        $persona = $utente->getPersona();
        $incarichi = $persona->getIncarichiPersone();
        foreach ($incarichi as $incarico) {
            if (true == $incarico->isAttivo()) {
                $incarichiArray[] = $incarico;
            }
        }
        return $incarichiArray;
    }

    /**
     * @return IncaricoPersona[]
     */
    protected function getIncarichiSuSoggetto(Soggetto $soggetto, Utente $utente): array {
        $intersezione = array_intersect($this->getIncaricoDaSoggetto($soggetto), $this->getIncaricoPersona($utente));
        return $intersezione;
    }
}
