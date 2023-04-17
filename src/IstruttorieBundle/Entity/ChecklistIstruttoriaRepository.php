<?php

namespace IstruttorieBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use RichiesteBundle\Entity\Richiesta;

class ChecklistIstruttoriaRepository extends EntityRepository
{
    /**
     * @param Richiesta $richiesta
     * @return float|null
     */
    public function getImportoIrapAmmesso(Richiesta $richiesta)
    {
        $em = $this->getEntityManager();
        $rsm = new ResultSetMappingBuilder($this->getEntityManager());
        $nome_classe = get_class($richiesta->getOggettiRichiesta()->first());
        $nome_tabella_oggetto_irap = $em->getClassMetadata($nome_classe)->getTableName();

        $array_campi = ['importo_irap',];

        foreach ($array_campi as $campo ) {
            $rsm->addScalarResult($campo, $campo);
        }

        // Prendo nell'ordine:
        // - importo irap istruttoria se presente
        // - l'importo minore tra l'importo richiesto e quello fornito dall'Agenzia delle Entrate
        $sql = "
            SELECT 
                CASE
                    WHEN oggetto_irap.importo_irap_istruttoria IS NOT NULL THEN oggetto_irap.importo_irap_istruttoria
                    /* 
                    ~0 >> 1 serve per farsi dare il numero più grande possibile ed in questo modo evitare i valori NULL 
                    */
                    ELSE LEAST(IFNULL(oggetto_irap.importo_irap, ~0 >> 1), IFNULL(importo_irap.importo_irap, ~0 >> 1)) 
                END AS importo_irap
            FROM istruttorie_richieste AS istruttoria
            JOIN richieste AS richiesta ON (istruttoria.richiesta_id = richiesta.id)
            JOIN oggetti_richiesta AS oggetto_richiesta ON (richiesta.id = oggetto_richiesta.richiesta_id)
            JOIN " . $nome_tabella_oggetto_irap . " AS oggetto_irap ON (oggetto_richiesta.id = oggetto_irap.id)
            JOIN proponenti AS proponente ON (richiesta.id = proponente.richiesta_id AND proponente.mandatario = 1)
            JOIN soggetti AS soggetto ON (proponente.soggetto_id = soggetto.id)
            LEFT JOIN importi_irap AS importo_irap ON (COALESCE(soggetto.codice_fiscale, soggetto.partita_iva) = importo_irap.codice_fiscale)
            WHERE richiesta.id = :richiesta_id
        ";

        $query = $em->createNativeQuery($sql, $rsm);
        $query->setParameter('richiesta_id', $richiesta->getId());

        if (!empty($query->getResult())) {
            return $query->getResult()[0]['importo_irap'];
        } else {
            return null;
        }
    }
}
