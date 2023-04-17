<?php
namespace RichiesteBundle\Controller;

use BaseBundle\Controller\BaseController;

use DateTime;
use RichiesteBundle\Entity\Richiesta;
use MonitoraggioBundle\Entity\TC48TipoProceduraAttivazioneOriginaria;
use MonitoraggioBundle\Entity\TC10TipoLocalizzazione;
use MonitoraggioBundle\Entity\TC13GruppoVulnerabileProgetto;
use SfingeBundle\Entity\TipoAiuto;

class AbstractController extends BaseController {
    /**
     * @var Richiesta
     */
    protected $richiesta;

    protected function creaRichiesta(): Richiesta {
        $this->richiesta = new Richiesta();

        $tipoProceduraOriginaria = $this->getTipoProceduraOriginaria();
        $this->richiesta->setMonTipoProceduraAttOrig($tipoProceduraOriginaria);

        $tipoLocalizzazione = $this->getTipoLocalizzazione();
        $this->richiesta->setMonTipoLocalizzazione($tipoLocalizzazione);

        $vulnerabile = $this->getGruppoVulnerabile();
        $this->richiesta->setMonGruppoVulnerabile($vulnerabile);

        return $this->richiesta;
    }

    protected function getTipoProceduraOriginaria(string $codice = TC48TipoProceduraAttivazioneOriginaria::NON_RILEVANTE): TC48TipoProceduraAttivazioneOriginaria {
        /** @var TC48TipoProceduraAttivazioneOriginaria $tipoProcedura */
        $tipoProcedura = $this->getEm()->getRepository(TC48TipoProceduraAttivazioneOriginaria::class)->findOneBy([
            'tip_proc_att_orig' => $codice,
        ]);

        return $tipoProcedura;
    }

    protected function getTipoLocalizzazione(string $codice = TC10TipoLocalizzazione::PUNTUALE): TC10TipoLocalizzazione {
        /** @var TC10TipoLocalizzazione $tipoProcedura */
        $tipoProcedura = $this->getEm()->getRepository(TC10TipoLocalizzazione::class)->findOneBy([
            'tipo_localizzazione' => $codice,
        ]);

        return $tipoProcedura;
    }

    public function getGruppoVulnerabile(string $codice = TC13GruppoVulnerabileProgetto::NO_VULNERABILE): TC13GruppoVulnerabileProgetto {
        /** @var TC13GruppoVulnerabileProgetto $tipoProcedura */
        $gruppoVulnerabile = $this->getEm()->getRepository(TC13GruppoVulnerabileProgetto::class)->findOneBy([
            'cod_vulnerabili' => $codice,
        ]);

        return $gruppoVulnerabile;
    }

    protected function setInfoRichiestaDaProcedura() {
        if (\is_null($this->richiesta)) {
            throw new \Exception("E' necessario utilizzare il metodo creaRichiesta per inizializzare la richiesta");
        }

        $procedura = $this->richiesta->getProcedura();
        if (\is_null($procedura)) {
            throw new \Exception("La procedura è nulla");
        }
        /** @var TipoAIuto $tipoAiuto */
        $tipoAiuto = $procedura->getTipoAiuto()->first();
        if (false === $tipoAiuto) {
            throw new \Exception("Tipo aiuto non associato alla procedura");
        }
        $tc6TipoAiuto = $tipoAiuto->getTc6TipoAiuto();

        $this->richiesta->setMonTipoAiuto($tc6TipoAiuto);
    }

    /**
     * @param $id_richiesta
     * @return string
     */
    protected function getNomePdfMarcaDaBolloDigitale($id_richiesta): string
    {
        $date = new DateTime();
        $data = $date->format('d-m-Y');
        return "Documento marca da bollo digitale " . $id_richiesta . " " . $data;
    }
}
