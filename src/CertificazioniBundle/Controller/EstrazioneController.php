<?php

namespace CertificazioniBundle\Controller;

use BaseBundle\Service\SpreadsheetFactory;
use IstruttorieBundle\Entity\EsitoIstruttoria;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/estrazione")
 */
class EstrazioneController extends Controller {
    /**
     * @Route("/procedure", name="estrazione_procedure_certificazione")
     */
    public function estrazioneProcedure(): Response {
        /** @var SpreadsheetFactory $spreadSheetFactory */
        $spreadSheetFactory = $this->get('phpoffice.spreadsheet');
        $spreadSheet = $spreadSheetFactory->getSpreadSheet();
        $sheetProcedura = $spreadSheet->getActiveSheet();
        $this->estrazionePerProcedura($sheetProcedura);
        $sheetProgetto = $spreadSheet->createSheet();
        $this->estrazionePerProgetto($sheetProgetto);

        return $spreadSheetFactory->createResponse($spreadSheet, "estrazione_procedure.xlsx");
    }

    private function estrazionePerProcedura(Worksheet $sheet): void {
        $sheet->setTitle('Procedure');
        $sheet->fromArray([
            "Asse",
            //  "Procedura di aggiudicazione", // ??? chiedere lumi
            "Bando/procedura",
            "Numero domande ricevute",
            "Numero domande ammesse",
            "Numero domande ammesse e finanziate",
        ]);
        $qb = $this->getDoctrine()->getEntityManager()->createQueryBuilder();
        $expr = $qb->expr();
        $qb->select(
            'asse.titolo as titolo_asse',
            //procedura agg.
            'procedura.titolo as titolo_procedura',
            "count(richiesta.id) as domande_ricevute",
            "count(esito_istruttoria.id) as domande_ammesse",
            'SUM(case when esito_istruttoria.id is not null and 
                        istruttoria.ammissibilita_atto = 1 and 
                        istruttoria.concessione = 1 
                    then 1 else 0 end
            ) as domande_finanziate'
        )
        ->from('RichiesteBundle:Richiesta', 'richiesta')
        ->innerJoin('richiesta.procedura', ' procedura')
        ->innerJoin('procedura.asse', 'asse')
        ->leftJoin('richiesta.istruttoria', 'istruttoria')
        ->leftJoin('istruttoria.esito', 'esito_istruttoria', 'WITH', $expr->eq('esito_istruttoria.codice', $expr->literal(EsitoIstruttoria::AMMESSO)))
        ->leftJoin('richiesta.attuazione_controllo', 'atc')
        ->where(
            $expr->eq('richiesta.flag_por', true)
        )
        ->groupBy('procedura.id')
        ->orderBy('asse.id', 'ASC')
        ->orderBy('procedura.id', 'ASC');

        $result = $qb->getQuery()->getResult();
        $sheet->fromArray($result, null, 'A2', true);
    }

    public function estrazionePerProgetto(Worksheet $sheet): void {
        $sheet->setTitle('Progetti');

        $sheet->fromArray([
            "Asse",
            "Bando/procedura",
            'ID Operazione',
            'Protocollo',
            "Importo ammesso (totale progetto)",
            "Contributo concesso",
            "Importo rendicontato ammesso",
            "Importo pagato",
            "Importo certificato",
            "numero di certificazione",
            "Importo revocato",
            "Impegnato (da monitoraggio)",
            "Procedura di aggiudicazione (CIG)",
        ]);
        $qb = $this->getDoctrine()->getEntityManager()->createQueryBuilder();
        $expr = $qb->expr();
        $qb->select(
            'asse.titolo as titolo_asse',
            'procedura.titolo as titolo_procedura',
            'richiesta.id',
            "COALESCE(CONCAT(protocollo.registro_pg, '/', protocollo.anno_pg, '/', protocollo.num_pg), richiesta.id) as num_protocollo",
            "coalesce(variazione_piano.costo_ammesso, istruttoria.costo_ammesso, 0) as importo_ammesso",
            "coalesce(variazione_piano.contributo_ammesso, istruttoria.costo_ammesso, 0) as contributo_concesso",
            "(
                select SUM(COALESCE(elementi.valore_raw, 0))
                from AttuazioneControlloBundle:Pagamento p
                inner join p.valutazioni_checklist as valutazioni WITH valutazioni.validata = 1
                inner join valutazioni.valutazioni_elementi as elementi
                inner join elementi.elemento as def WITH def.codice = 'SPESE_AMMESSE'
                where p.attuazione_controllo_richiesta = atc.id
            ) as rendicontato_ammesso",
            "(
                select SUM(COALESCE(mandato.importo_pagato, 0))
                from AttuazioneControlloBundle:MandatoPagamento as mandato
                inner join mandato.pagamento pagamento
                where pagamento.attuazione_controllo_richiesta = atc.id
            ) as importo_pagato",
            "(
                select SUM(COALESCE(pagamento_cert.importo_certificato, 0))
                from AttuazioneControlloBundle:Pagamento as pagamento_cert
                inner join pagamento_cert.mandato_pagamento mand
                where pagamento_cert.attuazione_controllo_richiesta = atc.id
            ) as importo_certificato",
            "(
                select GROUP_CONCAT(
                    DISTINCT
                    CONCAT(certificazione.numero, '/', certificazione.anno)
                    SEPARATOR ', ')
                from CertificazioniBundle:Certificazione certificazione
                inner join certificazione.pagamenti pc
                inner join pc.pagamento cp
                where cp.attuazione_controllo_richiesta = atc
                
            ) as numero_certificazione",
            "(
                select SUM(coalesce(revoca.contributo_revocato, 0))
                from AttuazioneControlloBundle\Entity\Revoche\Revoca revoca
                inner join revoca.atto_revoca atto
                where revoca.attuazione_controllo_richiesta = atc
            ) as importo_revocato",
            "(
                select SUM(
                    COALESCE(impegni.importo_impegno, 0) * 
                    CASE impegni.tipologia_impegno
                            WHEN 'I' then 1
                            WHEN 'I-TR' then 1
                            WHEN 'D' then -1
                            WHEN 'D-TR' then -1
                            ELSE 0
                    END
                )
                from AttuazioneControlloBundle:RichiestaImpegni impegni
                where impegni.richiesta = richiesta
            ) as impegno",
            "(
                select GROUP_CONCAT(proAgg.cig SEPARATOR ', ')
                from AttuazioneControlloBundle:ProceduraAggiudicazione proAgg
                where proAgg.richiesta = richiesta
            ) as cig"
            )->from(
                'RichiesteBundle:Richiesta', 'richiesta'
            )
            ->innerJoin('richiesta.procedura', ' procedura')

        ->innerJoin('procedura.asse', 'asse')
        ->innerJoin('richiesta.istruttoria', 'istruttoria')
        ->innerJoin('richiesta.attuazione_controllo', 'atc')
        ->innerJoin('richiesta.richieste_protocollo', 'protocollo')
        ->leftJoin('atc.variazioni', 'variazione', 'WITH',
                    "variazione.id = (
                        select MAX(vpc.id)
                        from AttuazioneControlloBundle:VariazionePianoCosti vpc
                        inner join vpc.stato statoVariazione1
                        where vpc.esito_istruttoria = 1  
                        and statoVariazione1.codice = 'VAR_PROTOCOLLATA'
                        and vpc.attuazione_controllo_richiesta = atc
                    )"
        )
        ->leftJoin('AttuazioneControlloBundle:VariazionePianoCosti', 'variazione_piano', 'WITH', 
                    "
                    variazione_piano.id = variazione.id
                    ")
        ->where(
            $expr->eq('richiesta.flag_por', true)
        )
        ->groupBy('richiesta.id')
        ->orderBy('asse.id', 'ASC')
        ->orderBy('procedura.id', 'ASC');

        $result = $qb->getQuery()->getResult();
        $sheet->fromArray($result, null, 'A2', true);
    }
}
