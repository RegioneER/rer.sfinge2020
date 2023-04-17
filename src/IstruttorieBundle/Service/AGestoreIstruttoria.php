<?php

namespace IstruttorieBundle\Service;

use BaseBundle\Service\BaseService;
use RichiesteBundle\Entity\Richiesta;
use AttuazioneControlloBundle\Entity\StrumentoAttuativo;
use AttuazioneControlloBundle\Entity\RichiestaImpegni;
use AttuazioneControlloBundle\Entity\ImpegniAmmessi;
use AttuazioneControlloBundle\Entity\RichiestaLivelloGerarchico;
use AttuazioneControlloBundle\Entity\RichiestaProgramma;
use MonitoraggioBundle\Entity\TC12Classificazione;
use AttuazioneControlloBundle\Entity\RichiestaProgrammaClassificazione;
use MonitoraggioBundle\Entity\TC11TipoClassificazione;
use RichiesteBundle\Entity\IndicatoreOutput;
use Doctrine\Common\Collections\Collection;
use MonitoraggioBundle\Entity\TC44_45IndicatoriOutput;
use MonitoraggioBundle\Entity\TC36LivelloGerarchico;
use SoggettoBundle\Entity\ComuneUnione;
use AttuazioneControlloBundle\Entity\Finanziamento;
use MonitoraggioBundle\Entity\TC33FonteFinanziaria;
use SoggettoBundle\Entity\Soggetto;
use RichiesteBundle\Entity\IndicatoreRisultato;
use MonitoraggioBundle\Entity\TC42_43IndicatoriRisultato;

abstract class AGestoreIstruttoria extends BaseService implements IGestoreIstruttoria {

    protected function isFaseCompleta($istruttoria) {
        $fase = $istruttoria->getFase();
        if (!is_null($fase->getChecklist())) {
            foreach ($fase->getChecklist() as $checklist) {
                $valutazioni_checklist = $this->findValutazioniChecklist($istruttoria, $checklist);
                foreach ($valutazioni_checklist as $valutazione_checklist) {
                    if (!$valutazione_checklist->getValidata()) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    protected function findValutazioniChecklist($istruttoria_richiesta, $checklist) {
        return $this->getEm()->getRepository("IstruttorieBundle\Entity\ValutazioneChecklistIstruttoria")->findBy(["istruttoria" => $istruttoria_richiesta, "checklist" => $checklist]);
    }

    protected function salvaContributo($istruttoria_richiesta, $contributo) {
        $istruttoria_richiesta->setContributoAmmesso($contributo);
    }

    protected function popolaSoggettiCollegati(Richiesta $richiesta) {
        throw new \Exception("Metodo non implementato");
    }

    protected function popolaStatoAttuazioneProgetto(Richiesta $richiesta) {
        throw new \Exception("Metodo non implementato");
    }

    protected function popolaRichiestaProgrammi(Richiesta $richiesta) {
        throw new \Exception("Metodo non implementato");
    }

    protected function popolaPianoCosti(Richiesta $richiesta) {
        throw new \Exception("Metodo non implementato");
    }

    protected function popolaVociSpesa(Richiesta $richiesta) {
        throw new \Exception("Metodo non implementato");
    }

    protected function popolaRichiestaStrumentoAttuativo(Richiesta $richiesta): Richiesta {
        $tipoStrumentoAttuativo = $this->getEm()->getRepository("MonitoraggioBundle:TC15StrumentoAttuativo")
            ->findOneBy(["cod_stru_att" => "01"]);
        $strumentoAttuativo = new StrumentoAttuativo($richiesta, $tipoStrumentoAttuativo);
        $richiesta->addMonStrumentiAttuativi($strumentoAttuativo);

        return $richiesta;
    }

    public function creaImpegno(Richiesta $richiesta): RichiestaImpegni {
        $istruttoria = $richiesta->getIstruttoria();

        $impegno = new RichiestaImpegni($richiesta);
        $impegno->setDataImpegno($istruttoria->getDataImpegno())
        ->setTipologiaImpegno(RichiestaImpegni::IMPEGNO)
        ->setImportoImpegno($istruttoria->getImpegnoAmmesso());

        return $impegno;
    }

    public function creaImpegnoAmmesso(RichiestaImpegni $impegno, RichiestaLivelloGerarchico $livelloGerarchico): ImpegniAmmessi {
        $impegnoAmmesso = new ImpegniAmmessi($impegno, $livelloGerarchico);
        $impegnoAmmesso->setImportoImpAmm($impegno->getImportoImpegno())
        ->setTipologiaImpAmm($impegno->getTipologiaImpegno());
        return $impegnoAmmesso;
    }

    public function creaLivelloGerarchicoPerAsse(RichiestaProgramma $programma): RichiestaLivelloGerarchico {
        $procedura = $programma->getRichiesta()->getProcedura();
        $livelloAssociatoAsAsse =$procedura->getAsse()->getLivelloGerarchico();

        $livello = new RichiestaLivelloGerarchico($programma, $livelloAssociatoAsAsse);

        return $livello;
    }

    /**
     * @return Collection|RichiestaLivelloGerarchico[]
     */
    protected function creaLivelloGerarchicoPerObiettiviSpecifici(RichiestaProgramma $programma): Collection{
        return $programma->getRichiesta()->getProcedura()->getLivelliGerarchici()->map(
            function(TC36LivelloGerarchico $l) use ($programma){
			    return new RichiestaLivelloGerarchico($programma, $l);
		});
    }

    /**
     * @param Richiesta $richiesta
     * @return RichiestaProgramma
     */
    public function creaProgramma( Richiesta $richiesta){
        $richiestaProgrammaMon = $richiesta->getMonProgrammi()->first();
        if($richiestaProgrammaMon){
            return $richiestaProgrammaMon;
        }
        $programma = $this->getEm()->getRepository("MonitoraggioBundle:TC4Programma")->findOneBy(array("cod_programma" => "2014IT16RFOP008"));
        
        $richiestaProgrammaMon = new RichiestaProgramma($richiesta);
        $richiestaProgrammaMon->setTc4Programma($programma);
        $richiestaProgrammaMon->setStato(RichiestaProgramma::STATO_ATTIVO);

        return $richiestaProgrammaMon;
    }

    /**
     * @return RichiestaProgrammaClassificazione[]
     */
    public function creaClassificazioni(RichiestaProgramma $programma): array {
        $classificazioniRichiesta = [];
        $richiesta = $programma->getRichiesta();
        $procedura = $richiesta->getProcedura();
        $azioni = $procedura->getAzioni();
        foreach ($azioni as $azione) {
            $classificazioni = $azione->getClassificazioni();
            foreach ($classificazioni as $classificazione) {
                if ($this->isClassificazioneDaAggiungere($classificazione, $programma)) {
                    $nuovaClassificazione = new RichiestaProgrammaClassificazione($programma);
                    $nuovaClassificazione->setClassificazione($classificazione);
                    $classificazioniRichiesta[] = $nuovaClassificazione;
                }
            }
        }
        return $classificazioniRichiesta;
    }

    public function isClassificazioneDaAggiungere(TC12Classificazione $classificazione, RichiestaProgramma $richiestaProgramma): bool {
        $programma = $classificazione->getProgramma();
        $tipoClassificazione = $classificazione->getTipoClassificazione();
        $codice = $tipoClassificazione->getTipoClass();
        return  (TC11TipoClassificazione::RISULTATO_ATTESO == $codice ||
            TC11TipoClassificazione::LINEA_AZIONE == $codice) && (
                \is_null($programma) ||
                $programma == $richiestaProgramma->getTc4Programma()
            );
    }

    public function popolaIndicatoriOutput(Richiesta $richiesta): void {

        $indicatoriService = $this->container->get('monitoraggio.indicatori_output')->getGestore($richiesta);
		$indicatoriService->popolaIndicatoriOutput();        
    }

    public function popolaIndicatoriRisultato(Richiesta $richiesta): void {
        $procedura = $richiesta->getProcedura();
        $indicatoriRisultatoProcedura = $procedura->getIndicatoriRisultato();

        $indicatoriRisultatoRichiesta = $richiesta->getMonIndicatoreRisultato()->map(function(IndicatoreRisultato $indicatore){
            return $indicatore->getIndicatore();
        })->toArray();

        $indicatoriDaAggiungere = \array_diff($indicatoriRisultatoProcedura, $indicatoriRisultatoRichiesta);

        $indicatori = \array_map(function(TC42_43IndicatoriRisultato $def) use($richiesta): IndicatoreRisultato {
            return new IndicatoreRisultato($richiesta,$def);
        }, $indicatoriDaAggiungere);

        \array_walk($indicatori, function(IndicatoreRisultato $indicatore, $key, Richiesta $richiesta){
            $richiesta->addMonIndicatoreRisultato($indicatore);
        },$richiesta);
    }

    protected function popolaProgrammi(Richiesta $richiesta): Collection {
        $programmiRichiesta = $richiesta->getMonProgrammi();
        $programmiProcedura = $richiesta->getProcedura()
            ->getMonProcedureProgrammi();
        foreach ($programmiProcedura as $programma) {
            if (false == $programmiRichiesta->contains($programma)) {
                $programmiRichiesta->add($programma);
            }
        }

        return $programmiRichiesta;
    }

    /**
	 * @param Richiesta $richiesta
	 * @return Richiesta
	 *
	 * Percentuali su contributo concesso: UE = 50%, STATO = 35%, REGIONE = 15%
	 */
	protected function popolaFinanziamento(Richiesta $richiesta): Richiesta {
        $gestoreFinanziamentoService = $this->container->get('monitoraggio.gestore_finanziamento');
        /** @var \MonitoraggioBundle\Service\IGestoreFinanziamento $gestoreFinanziamento */
		$gestoreFinanziamento = $gestoreFinanziamentoService->getGestore($richiesta);
		$gestoreFinanziamento->aggiornaFinanziamento();
		$gestoreFinanziamento->persistFinanziamenti();

		return $richiesta;
	}
}
