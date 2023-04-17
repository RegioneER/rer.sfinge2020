<?php

namespace MonitoraggioBundle\Repository;

use Doctrine\ORM\EntityRepository;
use AttuazioneControlloBundle\Entity\RichiestaProgramma;
use MonitoraggioBundle\Entity\TC11TipoClassificazione;
use MonitoraggioBundle\Form\Entity\TabelleContesto\TC36;
use Doctrine\ORM\QueryBuilder;
use SfingeBundle\Entity\Procedura;
use MonitoraggioBundle\Entity\TC36LivelloGerarchico;
use Doctrine\ORM\Query;

class TC36LivelloGerarchicoRepository extends EntityRepository {
    public function findAllFiltered(TC36 $ricerca): Query {
        $q = $this->getEntityManager()->createQuery();
        $query = 'select e '
                . 'from MonitoraggioBundle:TC36LivelloGerarchico  e '
                . "where e.cod_liv_gerarchico like :cod_liv_gerarchico "
                . "and coalesce(e.valore_dati_rilevati, '') like :valore_dati_rilevati "
                . "and coalesce(e.descrizione_codice_livello_gerarchico, '') like :descrizione_codice_livello_gerarchico "
                . "and coalesce(e.cod_struttura_prot, '') like :cod_struttura_prot "
                . "order by e.id asc";
        $q->setDQL($query);
        $q->setParameter(':cod_liv_gerarchico', '%' . $ricerca->getCodLivGerarchico() . '%');
        $q->setParameter(':valore_dati_rilevati', '%' . $ricerca->getValoreDatiRilevati() . '%');
        $q->setParameter(':cod_struttura_prot', '%' . $ricerca->getCodStrutturaProt() . '%');
        $q->setParameter(':descrizione_codice_livello_gerarchico', '%' . $ricerca->getDescrizioneCodiceLivelloGerarchico() . '%');

        return $q;
    }

    public function queryStrutturaProtocollo($tabelle): QueryBuilder {
        $q = $this->getEntityManager()->createQueryBuilder('u');
        $q->select('u');
        $q->from('MonitoraggioBundle:TC36LivelloGerarchico', 'u');
        if ($tabelle) {
            foreach ($tabelle as $key => $tabella) {
                $q->andWhere($q->expr()->like('u.cod_struttura_prot', '?' . $key));
                $q->setParameter($key, '%' . $tabella . '%');
            }
        }

        return $q;
    }

    public function findbyStrutturaProtocollo($tabelle) {
        return $this->queryStrutturaProtocollo($tabelle)->getQuery()->getResult();
    }

    public function ajaxRequest($params) {
        $q = $this->getEntityManager()->createQueryBuilder('u');
        $q->select(['u.id', 'u.descrizione_codice_livello_gerarchico value']);
        $q->where($q->expr()->eq(1, 1));
        if ($params) {
            foreach ($params as $key => $tabella) {
                $q->andWhere($q->expr()->like('u.cod_struttura_prot', '?' . $key));
                $q->setParameter($key, '%' . $tabella . '%');
            }
        }
        return $q->getQuery()->getArrayResult();
    }

    // Funzione per il monitoraggio: devo recuperare il livello gerarchico in base all'asse per popolare la tabella RichiestaSpesaCertificata(FN09)
    public function getLivelloGerarchicoFN09ByAsse($idAsse) {
        $codiceLivelloGerarchico = "2014IT16RFOP008_" . $idAsse;
        $codiceStrutturaProtocollo = "FN09;";
        $dql = "SELECT e "
                . "FROM MonitoraggioBundle:TC36LivelloGerarchico e "
                . "WHERE e.cod_liv_gerarchico = :codiceLivelloGerarchico "
                . "AND e.cod_struttura_prot = :codiceStrutturaProtocollo ";
        $q = $this->getEntityManager()->createQuery();
        $q->setParameter(":codiceLivelloGerarchico", $codiceLivelloGerarchico);
        $q->setParameter(":codiceStrutturaProtocollo", $codiceStrutturaProtocollo);
        $q->setDQL($dql);

        $result = $q->getResult();

        return $result[0]; // Dovrebbe essercene uno solo...
    }

    
    public function livelliPerRichiestaProgrammaQueryBuilder(RichiestaProgramma $programma): QueryBuilder {
        $qb = $this->createQueryBuilder('livello');
        $expr = $qb->expr();

        return $qb
            ->from('AttuazioneControlloBundle:RichiestaProgramma', 'programma')
            ->join('programma.richiesta', 'richiesta')
            ->join('richiesta.procedura', 'procedura')
            ->join('procedura.azioni', 'azioni')
            ->join('azioni.obiettivo_specifico', 'obiettivo')
            ->join('obiettivo.asse', 'asse')
            ->leftJoin('programma.mon_livelli_gerarchici', 'richieste_livelli_gerarchici')
            ->leftJoin('programma.classificazioni', 'classificazioni')
            ->leftJoin('classificazioni.classificazione', 'tc12')
            ->leftJoin('tc12.tipo_classificazione', 'tipo_classificazione')
            ->leftJoin('tc12.livello_gerarchico', 'livelliDaRisultatoAtteso')
            ->where(
                $expr->eq('programma', ':programma'),
                $expr->orX(
                    $expr->andX(
                        $expr->eq('tipo_classificazione.tipo_class', ':RisultatoAtteso'),
                        $expr->eq('livello', 'livelliDaRisultatoAtteso')
                    ),
                    $expr->eq('livello', 'asse.livello_gerarchico'),
                    $expr->eq('livello', 'obiettivo.livello_gerarchico'),
                    $expr->eq('livello', 'richieste_livelli_gerarchici.tc36_livello_gerarchico')
                )
            )
            ->setParameter('programma', $programma)
            ->setParameter('RisultatoAtteso', TC11TipoClassificazione::RISULTATO_ATTESO);
    }

    public function getLivelloPerAsseProcedura(Procedura $procedura): ?TC36LivelloGerarchico {
       
        $dql = "SELECT livello "
                . "FROM MonitoraggioBundle:TC36LivelloGerarchico livello "
                . "JOIN livello.assi assi "
                . "JOIN assi.procedure procedura "
                . "WHERE procedura = :procedura";
        return $this->getEntityManager()->createQuery($dql)->setParameter('procedura', $procedura)->getOneOrNullResult();
    }
}
