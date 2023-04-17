<?php

namespace MonitoraggioBundle\GestoriEsportazione\EsportaElementi;

use MonitoraggioBundle\Exception\EsportazioneException;
use MonitoraggioBundle\Entity\FN09SpeseCertificate;
use MonitoraggioBundle\Model\SpesaCertificata;
use RichiesteBundle\Entity\Richiesta;



/**
 * @author vbuscemi
 */
class EsportaFN09 extends Esporta {
    public function execute(Richiesta $richiesta, $tavola, $enable_ctrl = false) {
        if ($enable_ctrl) {
            if (!$this->em->getRepository('MonitoraggioBundle:FN09SpeseCertificate')->isEsportabile($tavola->getMonitoraggioConfigurazioneEsportazione())) {
                throw EsportazioneException::richiestaNonEsportabile('FN09', $richiesta);
            }
        }
        /** @var \CertificazioneBundle\Repository\CertificazionePagamento $certificazioniRepository */
        $certificazioniRepository = $this->em->getRepository('CertificazioneBundle:CertificazionePagamento');
        $speseCertificate = $certificazioniRepository->findAllSpeseCertificate($richiesta);
        $em = $this->em;
        $arrayRisultato = \array_map(function (SpesaCertificata $spesaCertificata) use ($richiesta, $em) {
            $domandaPagamento = $em->getRepository('MonitoraggioBundle:TC41DomandaPagamento')->findOneBy(array(
                'id_domanda_pagamento' => $spesaCertificata->getIdDomandaPagamento(),
            ));
            if (\is_null($domandaPagamento)) {
                throw new EsportazioneException('Domanda di pagamento "' . $spesaCertificata->getIdDomandaPagamento() . '" non definita a sistema');
            }
            
            $res = new FN09SpeseCertificate();
            $res->setCodLocaleProgetto($richiesta->getProtocollo())
            ->setDataDomanda($spesaCertificata->getDataDomanda())
            ->setTc41DomandePagamento($domandaPagamento)
            ->setTipologiaImporto($spesaCertificata->getTipologiaImporto())
            ->setImportoSpesaPub($spesaCertificata->getImportoSpesaPubblica())
            ->setImportoSpesaTot($spesaCertificata->getImportoTotale())
            ->setTc36LivelloGerarchico($spesaCertificata->getLivelloGerarchico());;

            return $res;
        }, $speseCertificate);

        return $arrayRisultato;
    }

    /**
     * @return FN09SpeseCertificate
     */
    public function importa($input_array) {
        if (is_null($input_array) || !is_array($input_array) || 8 != count($input_array)) {
            throw new EsportazioneException("FN09: Input_array non valido");
        }

        $res = new FN09SpeseCertificate();
        $res->setCodLocaleProgetto($input_array[0]);
        $data = $this->createFromFormatV2($input_array[1]);
        $res->setDataDomanda($data);
        $tc41 = $this->em->getRepository('MonitoraggioBundle:TC41DomandaPagamento')->findOneBy(array('id_domanda_pagamento' => $input_array[2]));
        if (\is_null($tc41)) {
            throw new EsportazioneException("Domanda pagamento non valida");
        }
        $res->setTc41DomandePagamento($tc41);
        $res->setTipologiaImporto($input_array[3]);
        $tc36 = $this->em->getRepository('MonitoraggioBundle:TC36LivelloGerarchico')->findOneBy(array('cod_liv_gerarchico' => $input_array[4]));
        if (\is_null($tc36)) {
            throw new EsportazioneException("Livello gerarchico non valido");
        }
        $res->setTc36LivelloGerarchico($tc36);
        $res->setImportoSpesaTot($input_array[5]);
        $res->setImportoSpesaPub($input_array[6]);
        $res->setFlgCancellazione($input_array[7]);
        return $res;
    }
}
