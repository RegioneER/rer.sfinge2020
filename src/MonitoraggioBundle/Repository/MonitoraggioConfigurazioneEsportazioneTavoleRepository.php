<?php

/**
 * Description of MonitoraggioConfigurazioneEsportazioneTavoleRepository.
 *
 * @author lfontana
 */

 /*
       protected static $ENTITY_REPOSITORY = array(
        'AP00' => 'AP00AnagraficaProgetti',
        'AP01' => 'AP01AssociazioneProgettiProcedura',
        'AP02' => 'AP02InformazioniGenerali',
        'AP03' => 'AP03Classificazioni',
        'AP04' => 'AP04Programma',
        'AP05' => 'AP05StrumentoAttuativo',
        'AP06' => 'AP06LocalizzazioneGeografica',
        'PG00' => 'PG00ProcedureAggiudicazione',
        'SC00' => 'SC00SoggettiCollegati',
        'IN00' => 'IN00IndicatoriRisultato',
        'IN01' => 'IN01IndicatoriOutput',
        'FN00' => 'FN00Finanziamento',
        'FN01' => 'FN01CostoAmmesso',
        'FN10' => 'FN10Economie',
        'FN02' => 'FN02QuadroEconomico',
        'FN03' => 'FN03PianoCosti',
        'FN04' => 'FN04Impegni',
        'FN05' => 'FN05ImpegniAmmessi',
        'FN06' => 'FN06Pagamenti',
        'FN07' => 'FN07PagamentiAmmessi',
        'FN08' => 'FN08Percettori',
        'FN09' => 'FN09SpeseCertificate',
        'PR00' => 'PR00IterProgetto',
        'PR01' => 'PR01StatoAttuazioneProgetto',
        'TR00' => 'Trasferimento',
        'PA00' => 'PA00ProcedureAttivazione',
        'PA01' => 'PA01ProgrammiCollegatiProceduraAttivazione',
    );

 */

namespace MonitoraggioBundle\Repository;

use Doctrine\ORM\EntityRepository;
use MonitoraggioBundle\Entity\MonitoraggioEsportazione as Esportazione;
use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneTavole as Tavola;
use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneTavole;
use MonitoraggioBundle\Exception\EsportazioneException;

class MonitoraggioConfigurazioneEsportazioneTavoleRepository extends EntityRepository
{
    protected static $eliminazione_strutture_correlate = array(
        'AP03' => array('AP00', 'AP01', 'AP02', 'AP03', 'AP04', 'AP05', 'AP06'),
    );



    public function findByProgressivoEsportazione($progressivo, $codice_struttura, Esportazione $esportazione)
    {
        $strutture = MonitoraggioEsportazioneRepository::GetAllStrutture();
        if (!\array_key_exists($codice_struttura, $strutture)) {
            throw new EsportazioneException('Codice struttura non presente nel sistema');
        }
        $struttura = $strutture[$codice_struttura];
        $dql = 'select tavola '
            . 'from MonitoraggioBundle:MonitoraggioConfigurazioneEsportazioneTavole tavola '
            . "join MonitoraggioBundle:$struttura struttura with tavola = struttura.monitoraggio_configurazione_esportazioni_tavola "
            // . "from MonitoraggioBundle:$struttura struttura "
            // . 'join struttura.monitoraggio_configurazione_esportazioni_tavola tavola '
            . 'join tavola.monitoraggio_configurazione_esportazione configurazione_esportazione '
            . 'where configurazione_esportazione.monitoraggio_esportazione = :esportazione '
            . 'and struttura.progressivo_puc = :progressivo';

        return $this->getEntityManager()
            ->createQuery($dql)
            ->setParameter('progressivo', $progressivo)
            ->setParameter('esportazione', $esportazione)
            ->getOneOrNullResult();
    }

    /**
     * @param MonitoraggioConfigurazioneEsportazioneTavole $tavola
     *
     * @return array
     */
    public function findStruttureByTavola(MonitoraggioConfigurazioneEsportazioneTavole $tavola)
    {
        $strutture = MonitoraggioEsportazioneRepository::GetAllStrutture();
        $classeStruttura = $strutture[$tavola->getTavolaProtocollo()];
        $dql = 'select struttura '
        . "from MonitoraggioBundle:$classeStruttura struttura "
        .'where struttura.monitoraggio_configurazione_esportazioni_tavola = :tavola ';

        return $this->getEntityManager()
        ->createQuery($dql)
        ->setParameter('tavola', $tavola)
        ->getResult();

    }

    /**
     * @param MonitoraggioConfigurazioneEsportazioneTavole $tavola          tavola collegata all'errore
     * @param string                                       $codiceStruttura codice della struttura
     * @param int                                          $progressivo     Progressivo PUC
     */
    public function updateStrutturaErroreIgrue(MonitoraggioConfigurazioneEsportazioneTavole $tavola, $codiceStruttura, $progressivo)
    {
        $strutture = MonitoraggioEsportazioneRepository::GetAllStrutture();
        $classeStruttura = $strutture[$codiceStruttura];
        $dql = "update MonitoraggioBundle:$classeStruttura struttura "
            . 'set struttura.flag_errore_igrue = 1 '
            . 'where struttura.monitoraggio_configurazione_esportazioni_tavola = :tavola '
            . 'and struttura.progressivo_puc = :progressivo ';

        return $this->getEntityManager()
        ->createQuery($dql)
        ->setParameter('tavola', $tavola)
        ->setParameter('progressivo', $progressivo)
        ->execute();
    }
}
