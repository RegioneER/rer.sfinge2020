<?php

namespace MonitoraggioBundle\Repository;

use Doctrine\ORM\EntityRepository;
use MonitoraggioBundle\Entity\MonitoraggioEsportazione;
use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneTrasferimento;
use MonitoraggioBundle\Entity\MonitoraggioEsportazioneLogFase;
use Doctrine\Common\Collections\ArrayCollection;
use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneRichiesta;
use RichiesteBundle\Entity\Richiesta;
use Doctrine\ORM\Query;
use MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneTavole;

class MonitoraggioEsportazioneRepository extends EntityRepository {
    public static $ENTITY_REPOSITORY = array(
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
                'FN02' => 'FN02QuadroEconomico',
                'FN03' => 'FN03PianoCosti',
                'FN04' => 'FN04Impegni',
                'FN05' => 'FN05ImpegniAmmessi',
                'FN06' => 'FN06Pagamenti',
                'FN07' => 'FN07PagamentiAmmessi',
                'FN08' => 'FN08Percettori',
                'FN09' => 'FN09SpeseCertificate',
                'FN10' => 'FN10Economie',
                'PR00' => 'PR00IterProgetto',
                'PR01' => 'PR01StatoAttuazioneProgetto',
        );

    public static $ENTITY_REPOSITORY_PROCEDURE = array(
                'PA00' => 'PA00ProcedureAttivazione',
                'PA01' => 'PA01ProgrammiCollegatiProceduraAttivazione',
        );
    public static $ENTITY_REPOSITORY_TRASFERIMENTI = array(
                'TR00' => 'TR00Trasferimenti',
        );

    protected static $ENTITY_REPOSITORY_SQL = array(
                'AP00' => 'ap00_anagrafica_progetti',
                'AP01' => 'ap01_associazione_progetti_procedura',
                'AP02' => 'ap02_informazioni_generali',
                'AP03' => 'ap03_classificazioni',
                'AP04' => 'ap04_programma',
                'AP05' => 'ap05_strumento_attuativo',
                'AP06' => 'ap06_localizzazione_geografica',
                'PG00' => 'pg00_procedura_aggiudicazione',
                'SC00' => 'sc00_soggetti_collegati',
                'IN00' => 'in00_indicatori_risultato',
                'IN01' => 'in01_indicatori_output',
                'FN00' => 'fn00_finanziamento',
                'FN01' => 'fn01_costo_ammesso',
                'FN10' => 'fn10_economie',
                'FN02' => 'fn02_quadro_economico',
                'FN03' => 'fn03_piano_costi',
                'FN04' => 'fn04_impegni',
                'FN05' => 'fn05_impegni_ammessi',
                'FN06' => 'fn06_pagamenti',
                'FN07' => 'fn07_pagamenti_ammessi',
                'FN08' => 'fn08_percettori',
                'FN09' => 'fn09_spese_certificate',
                'PR00' => 'pr00_iter_progetto',
                'PR01' => 'pr01_stato_attuazione_progetto',
                'TR00' => 'tr00_trasferimenti',
                'PA00' => 'pa00_procedure_attivazione',
                'PA01' => 'pa01_programmi_collegati_procedure_attivazione',
        );
    
    /**
     * @return array of object of \MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneRichiesta
     */
    public function findRichieste($esportazione)
    {
        $array_configurazioni = new ArrayCollection();

        $data = $this->getDataEsportazione($esportazione);

        foreach (self::$ENTITY_REPOSITORY as $key => $value) {
            $richieste = $this->getEntityManager()->getRepository('MonitoraggioBundle:' . $value)->findAllEsportabili($data);

            // per le varie richieste verifico se esiste la configurazione e nel caso la creo, poi aggiungo la nuova tavola
            foreach ($richieste as $richiesta) {
                $monitoraggio_configurazione_esportazione_richiesta = $this->findConfigurazioneEsportazioneRichiestaByRichiesta($richiesta[0], $esportazione);
                if (\is_null($monitoraggio_configurazione_esportazione_richiesta)) {
                    $monitoraggio_configurazione_esportazione_richiesta = new \MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneRichiesta();
                    $monitoraggio_configurazione_esportazione_richiesta->setElemento($richiesta[0]);
                    $monitoraggio_configurazione_esportazione_richiesta->setMonitoraggioEsportazione($esportazione);
                    $array_configurazioni->add($monitoraggio_configurazione_esportazione_richiesta);

                    $this->getEntityManager()->persist($monitoraggio_configurazione_esportazione_richiesta);
                    $this->getEntityManager()->flush($monitoraggio_configurazione_esportazione_richiesta);

                } else {
                    if (!$array_configurazioni->contains($monitoraggio_configurazione_esportazione_richiesta)) {
                        $array_configurazioni->add($monitoraggio_configurazione_esportazione_richiesta);
                        
                        $this->getEntityManager()->persist($monitoraggio_configurazione_esportazione_richiesta);
                        $this->getEntityManager()->flush($monitoraggio_configurazione_esportazione_richiesta);
                    }
                }

                $this->addTavolaToConfigurazione($key, $monitoraggio_configurazione_esportazione_richiesta);
            }
        }

        foreach ($array_configurazioni as $configurazione) {
            $configurazione = $configurazione->finalizedTavole();
            $this->getEntityManager()->persist($configurazione);
            $this->getEntityManager()->flush($configurazione);
        }

        return $array_configurazioni;
    }

    protected function configuraTavoleRichiesta(MonitoraggioConfigurazioneEsportazioneRichiesta $configurazione):void{
        $em = $this->getEntityManager();
        foreach (self::$ENTITY_REPOSITORY as $key => $value) {
            $isEsportabile = $em->getRepository('MonitoraggioBundle:'.$value)->isEsportabile($configurazione);
            $this->addTavolaToConfigurazione($key, $configurazione, $isEsportabile);
        }
    }

    /**
     * @return array of object of \MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneProcedura
     */
    public function findProcedure($esportazione) : array {
        $data = $this->getDataEsportazione($esportazione);

        $queryPa00 = 'select p as procedura, 0 as esportazione, 1 as PA00, 1 as PA01 '
                        . 'from SfingeBundle:Procedura p '
                        . 'where coalesce(p.data_modifica, p.data_creazione) > :data ';
        $resPa00 = $this->getEntityManager()
                        ->createQuery($queryPa00)
                        ->setParameter('data', $data)
                        ->getResult();

        $queryPa01 = 'select p as procedura, 0 as esportazione, 0 as PA00, 1 as PA01 '
                        . 'from SfingeBundle:Procedura p '
                        . 'left join p.mon_procedure_programmi mon_procedure_programmi '
                        . 'where coalesce(p.data_modifica, p.data_creazione) <= :data '
                        . 'and coalesce(mon_procedure_programmi.data_modifica, mon_procedure_programmi.data_creazione) > :data ';
        $resPa01 = $this->getEntityManager()
                        ->createQuery($queryPa01)
                        ->setParameter('data', $data)
                        ->getResult();

        $resProcedure = array_merge($resPa00, $resPa01);

        $array_configurazioni = array();
        foreach ($resProcedure as $value) {
            $array_configurazioni[] = new \MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneProcedura($value['procedura'], $esportazione, $value['PA00'], $value['PA01']);
        }

        return $array_configurazioni;
    }

    /**
     * @return array of object of \MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneTrasferimento
     */
    public function findTrasferimenti($esportazione) {
        $data = $this->getDataEsportazione($esportazione);

        $queryTrasferimenti = 'select t as trasferimento '
            . 'from MonitoraggioBundle:Trasferimento t '
            . 'where coalesce(t.data_modifica, t.data_creazione) > coalesce(:data, \'0000-00-00\') ';
        $resTrasferimenti = $this->getEntityManager()
            ->createQuery($queryTrasferimenti)
            ->setParameter('data', $data)
            ->getResult();

        $array_configurazioni = array();
        foreach ($resTrasferimenti as $value) {
            $array_configurazioni[] = new MonitoraggioConfigurazioneEsportazioneTrasferimento($value['trasferimento'], $esportazione, 1);
        }

        return $array_configurazioni;
    }

    public function findEsportazioneInCorso() {
        $queryData = 'select 1 '
            . 'from MonitoraggioBundle:MonitoraggioEsportazioneLogFase log_fase '
            . 'where log_fase.fase in (:inizializzazione, :scarico) '
            . 'and log_fase.data_fine is null ';

        $resData = $this->getEntityManager()
                ->createQuery($queryData)
                ->setParameter('inizializzazione', MonitoraggioEsportazioneLogFase::STATO_INIZIALIZZAZIONE)
                ->setParameter('scarico', MonitoraggioEsportazioneLogFase::STATO_SCARICO)
                ->setMaxResults(1)
                ->getOneOrNullResult();

        return is_null($resData) ? false : true;
    }

    public function findConfigurazioneEsportazioneRichiestaByRichiesta(Richiesta $richiesta, MonitoraggioEsportazione $esportazione): ?MonitoraggioConfigurazioneEsportazioneRichiesta {
        $query = 'select mcr '
            . 'from MonitoraggioBundle:MonitoraggioConfigurazioneEsportazioneRichiesta mcr '
            . 'where mcr.richiesta = :richiesta '
            . 'and mcr.monitoraggio_esportazione = :monitoraggio_esportazione';

        $res = $this->getEntityManager()
            ->createQuery($query)
            ->setParameter('richiesta', $richiesta)
            ->setParameter('monitoraggio_esportazione', $esportazione)
            ->getOneOrNullResult();

        return $res;
    }

    public function getConfigurazioneRichiesta(Richiesta $richiesta, MonitoraggioEsportazione $esportazione) : MonitoraggioConfigurazioneEsportazioneRichiesta{
        $configurazione = $this->findConfigurazioneEsportazioneRichiestaByRichiesta($richiesta, $esportazione);
        if (\is_null($configurazione)) {
            $configurazione = new MonitoraggioConfigurazioneEsportazioneRichiesta();
            $configurazione->setElemento($richiesta);
            $configurazione->setMonitoraggioEsportazione($esportazione);
            $esportazione->addMonitoraggioConfigurazione($configurazione);
        } 
        return $configurazione;
    }

    public function addTavolaToConfigurazione($struttura, &$configurazione, $value = true): void {
        // Creo la tavola relativa alla struttura e la aggiungo alla configurazione
        $tavola = new \MonitoraggioBundle\Entity\MonitoraggioConfigurazioneEsportazioneTavole();
        $tavola->setFlagEsportazione($value);
        $tavola->setMonitoraggioConfigurazioneEsportazione($configurazione);
        $tavola->setTavolaProtocollo($struttura);

        // Aggiungo la tavola alla configurazione
        $configurazione->addMonitoraggioConfigurazioneEsportazioneTavole($tavola);
        $configurazione->addStrutture($struttura);

        $this->getEntityManager()->persist($tavola);
        $this->getEntityManager()->flush($tavola);
    }

    public function getDataEsportazione($esportazione): string {
        $query = "SELECT COALESCE(MAX(me.data_creazione),'0000-00-00') data_creazione 
                FROM MonitoraggioBundle:MonitoraggioEsportazione me 
                INNER JOIN me.fasi fasi with fasi.data_cancellazione IS NULL
                WHERE me <> :esportazione 
                    AND fasi.fase = :STATO_INVIATO 
                    AND me.data_cancellazione IS NULL
        ";

        $res = $this->getEntityManager()
        ->createQuery($query)
        ->setParameter('esportazione', $esportazione)
        ->setParameter('STATO_INVIATO', MonitoraggioEsportazioneLogFase::STATO_INVIATO )
        ->getSingleResult();

        return  $res['data_creazione'];
    }
    /**
     * @return MonitoraggioConfigurazioneEsportazioneTavole[]
     */
    public function getAllTavoleByEsportazione($esportazione): ?array {
        $query = 'select distinct t, monitoraggio_configurazione_esportazione '
            . 'from MonitoraggioBundle:MonitoraggioConfigurazioneEsportazioneTavole t '
            . 'join t.monitoraggio_configurazione_esportazione monitoraggio_configurazione_esportazione '
            . 'join monitoraggio_configurazione_esportazione.monitoraggio_esportazione monitoraggio_esportazione '
            . 'where monitoraggio_esportazione = :monitoraggio_esportazione '
            . 'and coalesce( t.flag_conferma_esportazione, t.flag_esportazione ) = 1';

        $res = $this->getEntityManager()
                        ->createQuery($query)
                        ->setParameter('monitoraggio_esportazione', $esportazione)
                        ->getResult();

        return $res;
    }

    public function resetScarico($esportazione) {
        $count = 0;
        $conn = $this->getEntityManager()->getConnection();

        foreach (self::$ENTITY_REPOSITORY_SQL as $tabella) {
            $sqlStrutture = 'delete x from ' . $tabella . ' x '
                . 'join monitoraggio_configurazione_esportazioni_tavole mcet on mcet.id = x.monitoraggio_configurazione_esportazioni_tavola_id '
                . 'join monitoraggio_configurazione_esportazioni mce on mce.id = mcet.monitoraggio_configurazione_esportazione_id '
                . 'join monitoraggio_esportazioni me on me.id = mce.monitoraggio_esportazione_id '
                . 'where me.id = ? ';

            $count += $conn->executeUpdate($sqlStrutture, array($esportazione->getId()));
        }

        $sqlError = 'delete e from monitoraggio_configurazione_esportazione_errori e '
            . 'join monitoraggio_configurazione_esportazioni mce on mce.id = e.monitoraggio_configurazione_esportazione_id '
            . 'join monitoraggio_esportazioni me on me.id = mce.monitoraggio_esportazione_id '
            . 'where me.id = ? ';

        $count += $conn->executeUpdate($sqlError, array($esportazione->getId()));

        return $count;
    }

    /**
     * @param MonitoraggioEsportazione $esportazione
     *
     * @return MonitoraggioEsportazione
     */
    public function creaOggettoFormProcedura(MonitoraggioEsportazione $esportazione) {
        $dql = 'select conf_esporta, tavole, procedura '
            . 'from MonitoraggioBundle:MonitoraggioConfigurazioneEsportazioneProcedura conf_esporta '
            . 'join conf_esporta.monitoraggio_configurazione_esportazione_tavole tavole '
            . 'join conf_esporta.procedura procedura '
            . 'where conf_esporta.monitoraggio_esportazione = :monitoraggio_esportazione';
        $res = $this->getEntityManager()
            ->createQuery($dql)
            ->setParameter('monitoraggio_esportazione', $esportazione)
            ->getResult();
        $resEsportazione = new MonitoraggioEsportazione();
        $resEsportazione->setMonitoraggioConfigurazione(
            new \Doctrine\Common\Collections\ArrayCollection($res)
        );

        return $resEsportazione;
    }

    /**
     * @param MonitoraggioEsportazione $esportazione
     *
     * @return MonitoraggioEsportazione
     */
    public function creaOggettoFormTrasferimento(MonitoraggioEsportazione $esportazione) {
        $dql = 'select conf_esporta '
            . 'from MonitoraggioBundle:MonitoraggioConfigurazioneEsportazioneTrasferimento conf_esporta '
            . 'where conf_esporta.monitoraggio_esportazione = :monitoraggio_esportazione';
        $res = $this->getEntityManager()
            ->createQuery($dql)
            ->setParameter('monitoraggio_esportazione', $esportazione)
            ->getResult();
        $resEsportazione = new MonitoraggioEsportazione();
        $resEsportazione->setMonitoraggioConfigurazione(
                        new \Doctrine\Common\Collections\ArrayCollection($res)
                );

        return $resEsportazione;
    }

    public function queryCreaOggettoFormRichiesta(MonitoraggioEsportazione $esportazione) {
        $dql = 'select conf_esporta, tavola, richiesta, istruttoria, protocollo '
            . 'from MonitoraggioBundle:MonitoraggioConfigurazioneEsportazioneRichiesta conf_esporta '
            . 'join conf_esporta.monitoraggio_configurazione_esportazione_tavole tavola '
            . 'join conf_esporta.richiesta richiesta '
            . 'join richiesta.istruttoria istruttoria '
            . 'join richiesta.richieste_protocollo protocollo '
            . 'where conf_esporta.monitoraggio_esportazione = :monitoraggio_esportazione';
        $res = $this->getEntityManager()
            ->createQuery($dql)
            ->setParameter('monitoraggio_esportazione', $esportazione);
        return $res;
    }

    public function findAllStruttureByEsportazione(MonitoraggioEsportazione $esportazione) {
        $strutture = self::GetAllStrutture();
        $em = $this->getEntityManager();
        return \array_reduce($strutture, function ($iterator, $struttura) use ($em, $esportazione) {
            $result = $em->createQueryBuilder()
                ->select(array('u', 'tavola'))
                ->from('MonitoraggioBundle:' . $struttura, 'u')
                ->join('u.monitoraggio_configurazione_esportazioni_tavola', 'tavola')
                ->join('tavola.monitoraggio_configurazione_esportazione', 'configurazione')
                ->join('configurazione.monitoraggio_esportazione', 'esportazione')
                ->where('esportazione.id = :esportazione')
                ->setParameter('esportazione', $esportazione->getId())
                ->distinct()
                ->getQuery()
                ->iterate();

            if (!\is_null($result)) {
                $iterator->append($result);
            }

            return $iterator;
        }, new \AppendIterator());
    }

    public static function GetAllStrutture() : array {
        return \array_merge(self::$ENTITY_REPOSITORY, self::$ENTITY_REPOSITORY_PROCEDURE, self::$ENTITY_REPOSITORY_TRASFERIMENTI);
    }

    public function findAllEsportazioni(\MonitoraggioBundle\Form\Entity\RicercaEsportazione $fase) :Query {
        $dql = 'select distinct monitoraggio_esportazione '
            . 'from MonitoraggioBundle:MonitoraggioEsportazione monitoraggio_esportazione '
            . 'join monitoraggio_esportazione.fasi fasi '
            . 'where fasi.fase = coalesce(:fase, fasi.fase) '
            . 'and monitoraggio_esportazione.id = coalesce(:num_invio, monitoraggio_esportazione.id) '
            . 'order by monitoraggio_esportazione.id DESC';
        $res = $this->getEntityManager()
                    ->createQuery($dql)
                    ->setParameter('fase', $fase->getStato())
                    ->setParameter('num_invio', $fase->getNumInvio());

        return $res;
    }

    public function findStatoInCorso() : ?string {
        $dql = 'select fasi.fase '
        . 'from MonitoraggioBundle:MonitoraggioEsportazione e '
        . 'join e.fasi fasi '
        . 'where fasi.data_fine is null ';
        $res = $this->getEntityManager()
        ->createQuery($dql)
        ->setMaxResults(1)
        ->getOneOrNullResult();
        return \is_null($res) ? null : MonitoraggioEsportazioneLogFase::$FASI[$res['fase']];
    }

    public function canellaEsportazioneImportata(MonitoraggioEsportazione $esportazione) {
        $em = $this->getEntityManager();
        $connection = $em->getConnection();
        try {
            $connection->beginTransaction();
            //Cancello le strutture
            $strutture = $this->findAllStruttureByEsportazione($esportazione);
            while ($strutture->valid()) {
                $struttura = $strutture->current();
                $em->remove($struttura[0]);
                $em->flush($struttura[0]);
                $strutture->next();
            }

            //Cancello tutto il resto (tavole -> configurazioni -> esportazione) nota: ci sono i cascade remove !!!
            $em->remove($esportazione);
            $em->flush();
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw new \MonitoraggioBundle\Exception\EsportazioneException('Errore alla cancellazione dell\'importazione');
        }
        return true;
    }
}
