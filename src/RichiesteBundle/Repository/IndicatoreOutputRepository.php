<?php

namespace RichiesteBundle\Repository;
use Doctrine\ORM\EntityRepository;
use MonitoraggioBundle\Entity\IndicatoriOutputAzioni;

/**
 * @author afavilli
 */
class IndicatoreOutputRepository extends EntityRepository{
    public function applicaAssociazioneIndicatoriASistema(IndicatoriOutputAzioni $associazione): array {
        $da = $associazione->getValidoDa() ? $associazione->getValidoDa()->format('Y-m-d H:i:s') : null;
        $a = $associazione->getValidoA() ? $associazione->getValidoA()->format('Y-m-d H:i:s') : null;
        // Modifico gli indicatori già istanziati

        $sql = "UPDATE indicatori_output i
        INNER JOIN richieste as r
        ON r.id = i.richiesta_id
        AND r.data_cancellazione is null
        AND r.data_creazione between COALESCE(:da, '0000-01-01') and COALESCE(:a, '9999-12-31')
        join procedure_operative as po
        on po.id = r.procedura_id
        and po.asse_id = COALESCE(:asse, po.asse_id)
        join assi
        on assi.id = po.asse_id
        join procedure_operative_azioni as poa
        on poa.procedura_id = po.id
        and poa.azione_id = :azione
        set i.validoA = :a
        WHERE
        i.indicatore_id = :indicatore
        ";

        $modificati = $this->getEntityManager()->getConnection()->executeUpdate($sql, [
            'asse' => $associazione->getAsse()->getId(),
            'azione' => $associazione->getAzione()->getId(),
            'a' => $a,
            'da' => $da,
            'indicatore' => $associazione->getIndicatoreOutput()->getId(),
        ]);
        

        // Istanzio gli indicatori non ancora istanziati nei progetti
        $sql = "INSERT INTO indicatori_output(richiesta_id, indicatore_id, validoA, data_creazione)
        SELECT r.id, :indicatore, :a, NOW()
        FROM richieste AS r
        JOIN procedure_operative AS po
        on r.procedura_id = po.id
        JOIN assi
        ON assi.id = po.asse_id
        AND assi.id = COALESCE(:asse, assi.id)
        JOIN procedure_operative_azioni AS poa
        on poa.procedura_id = po.id
        and poa.azione_id = :azione
        WHERE
        r.data_cancellazione is null
        AND r.flag_por = 1
        AND r.data_creazione between COALESCE(:da, '0000-01-01') and COALESCE(:a, '9999-12-31')
        and 0 = (SELECT count(i1.id) 
            FROM indicatori_output as i1
            WHERE i1.data_cancellazione is null
            and i1.indicatore_id = :indicatore
            and i1.richiesta_id = r.id
        )
        ";

        $inseriti = $this->getEntityManager()->getConnection()->executeUpdate($sql, [
            'asse' => $associazione->getAsse()->getId(),
            'azione' => $associazione->getAzione()->getId(),
            'a' => $a,
            'da' => $da,
            'indicatore' => $associazione->getIndicatoreOutput()->getId(),
        ]);

        return [
            'modificati' => $modificati,
            'inseriti' => $inseriti,
        ];
    }
}
