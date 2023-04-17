<?php

namespace MonitoraggioBundle\GestoriEsportazione\EsportaElementi;

use Doctrine\Common\Collections\ArrayCollection;
use MonitoraggioBundle\Exception\EsportazioneException;
use RichiesteBundle\Entity\Richiesta;
use MonitoraggioBundle\Entity\FN08Percettori;
use AttuazioneControlloBundle\Entity\PagamentiPercettoriGiustificativo;
use AttuazioneControlloBundle\Entity\PagamentiPercettoriSoggetto;

class EsportaFN08 extends Esporta {
    /**
     * @return ArrayCollection|FN08Percettori[]
     */
    public function execute(Richiesta $richiesta, $tavola, $enable_ctrl = false) {
        if ($enable_ctrl) {
            if (!$this->em->getRepository('MonitoraggioBundle:FN08Percettori')->isEsportabile($tavola->getMonitoraggioConfigurazioneEsportazione(), $richiesta)) {
                throw EsportazioneException::richiestaNonEsportabile('FN08', $richiesta);
            }
        }
        $arrayRisultato = new ArrayCollection();
        foreach ($richiesta->getMonRichiestePagamento() as $pagamento) {
            foreach ($pagamento->getPercettori() as $percettore) {
                $res = new FN08Percettori();

                $res->setCodLocaleProgetto($richiesta->getProtocollo());
                $res->setCodPagamento($pagamento->getId());
                $res->setTipologiaPag($pagamento->getTipologiaPagamento());
                $res->setDataPagamento($pagamento->getDataPagamento());
                $res->setTc40TipoPercettore($percettore->getTipoPercettore());
                $res->setImporto($percettore->getImporto());
                $res->setFlgCancellazione(self::bool2SN(!is_null($percettore->getDataCancellazione())));
                $res->setEsportazioneStrutture($tavola);
                switch (\get_class($percettore)) {
                    case PagamentiPercettoriSoggetto::class:
                        /** @var PagamentiPercettoriSoggetto $percettore */
                        $soggetto = $percettore->getSoggetto();
                        if (\is_null($soggetto)) {
                            throw new EsportazioneException('Soggetto assente');
                        }
                        $formaGiuridica = $soggetto->getFormaGiuridica();
                        if (\is_null($formaGiuridica)) {
                            throw new EsportazioneException('Forma giuridica assente');
                        }
                        $res->setCodiceFiscale($soggetto->getCodiceFiscale());
                        $res->setFlagSoggettoPubblico(self::bool2SN($formaGiuridica->getSoggettoPubblico(), true));
                        break;
                    case PagamentiPercettoriGiustificativo::class:
                        /** @var PagamentiPercettoriGiustificativo $percettore */
                        $giustificativo = $percettore->getGiustificativoPagamento();
                        if (\is_null($giustificativo)) {
                            throw new EsportazioneException('Giustificativo assente');
                        }
                        $res->setCodiceFiscale($giustificativo->getCodiceFiscaleFornitore());
                        $res->setFlagSoggettoPubblico(self::bool2SN(false, true));
                        break;
                    default:
                        throw new EsportazioneException('Tipologia percettore non valida');
                }
                $arrayRisultato->add($res);
            }
        }
        return $arrayRisultato;
    }

    /**
     * @return FN08Percettori
     */
    public function importa($input_array) {
        if (\is_null($input_array) || !\is_array($input_array) || 9 != \count($input_array)) {
            throw new EsportazioneException("FN08: Input_array non valido");
        }

        $res = new FN08Percettori();
        $res->setCodLocaleProgetto($input_array[0]);
        $res->setCodPagamento($input_array[1]);
        $res->setTipologiaPag($input_array[2]);
        $data = $this->createFromFormatV2($input_array[3]);
        $res->setDataPagamento($data);
        $res->setCodiceFiscale($input_array[4]);
        $res->setFlagSoggettoPubblico($input_array[5]);
        $tc40 = $this->em->getRepository('MonitoraggioBundle:TC40TipoPercettore')->findOneBy(['tipo_percettore' => $input_array[6]]);
        if (\is_null($tc40)) {
            throw new EsportazioneException("Tipo percettore non valido");
        }
        $res->setTc40TipoPercettore($tc40);
        $res->setImporto($input_array[7]);
        $res->setFlgCancellazione($input_array[8]);
        return $res;
    }
}
