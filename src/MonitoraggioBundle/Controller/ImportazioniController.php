<?php

namespace MonitoraggioBundle\Controller;

use BaseBundle\Controller\BaseController;
use BaseBundle\Exception\SfingeException;
use BaseBundle\Service\SpreadsheetFactory;
use DocumentoBundle\Entity\DocumentoFile;
use DocumentoBundle\Entity\TipologiaDocumento;
use DocumentoBundle\Form\Type\DocumentoFileSimpleType;
use MonitoraggioBundle\Service\GestoreEsportazioneStruttureService;
use MonitoraggioBundle\Service\GestoreImportazioneMonitoraggio;
use PaginaBundle\Annotations\Breadcrumb;
use PaginaBundle\Annotations\ElementoBreadcrumb;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\PaginaInfo;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function PHPSTORM_META\map;

/**
 * @Route("/importazioni")
 */
class ImportazioniController extends BaseController {
    /**
     * @PaginaInfo(titolo="Importazioni", sottoTitolo="mostra l'elenco delle informazioni extrasistema")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/elenco", name="monitoraggio_elenco_importazioni")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco importazioni")})
     */
    public function elencoImportazioniAction(): Response {
        \set_time_limit(300);
        \ini_set('memory_limit', '1536M');
        /** @var GestoreEsportazioneStruttureService $gestoreEsportazione */
        $gestoreEsportazione = $this->container->get('monitoraggio.esportazione_strutture');
        $twigData = [
            'struttureEsportabili' => $gestoreEsportazione->getStruttureEsportabili(),
        ];
        return $this->render('MonitoraggioBundle:Importazioni:elenco.html.twig', $twigData);
    }

    /**
     * @PaginaInfo(titolo="Importazioni impegni e pagamenti", sottoTitolo="permette di caricare impegni e pagamenti multipli utilizzando un foglio excel")
     * @Menuitem(menuAttivo="monitoraggio_menu")
     * @Route("/importa_impegni_pagamenti", name="monitoraggio_elenco_importazioni_impegni_pagamenti")
     * @Breadcrumb(elementi={@ElementoBreadcrumb(testo="elenco importazioni")})
     */
    public function importazioneImpegniPagamentiAction(Request $request): Response {
        \set_time_limit(300);
        \ini_set('memory_limit', '512M');
        $tipologia = $this->getEm()->getRepository('DocumentoBundle:TipologiaDocumento')->findOneBy(['codice' => TipologiaDocumento::IMPORTAZIONE_IMPEGNI_PAGAMENTI]);
        $documento = new DocumentoFile($tipologia);
        $form = $this->createForm(DocumentoFileSimpleType::class, $documento)
            ->add('submit', SubmitType::class, [
                'label' => 'Carica',
            ])
            ->handleRequest($request);
        /** @var GestoreImportazioneMonitoraggio $gestore */
        $gestore = $this->get('monitoraggio.gestore_importazione');
        if ($form->isSubmitted() && $form->isValid()) {
            $logger = $this->get('logger');
            try {
                $this->get('documenti')->carica($documento);
                $gestore->importaImpegniPagamentiEnteGestore($documento);
            } catch (SfingeException $e) {
                $this->addError($e->getMessage());
            } catch (\Exception $e) {
                $logger->error($e->getMessage());
                $this->addError('Errore durante il caricamento o l\'elaborazione del documento');
            }
        }
        $optionBag = [
            'form' => $form->createView(),
            'warnings' => $gestore->getWarnings(),
        ];

        return $this->render('MonitoraggioBundle:Importazioni:importa_impegni_pagamenti.html.twig', $optionBag);
    }

    /**
     * @Route(
     *     "/estrazione/{struttura}",
     *     name="monitoraggio_estrazione_strutture"
     * )
     * @throws \ReflectionException
     */
    public function estrazioneStruttureAction(string $struttura): Response {
        /** @var GestoreEsportazioneStruttureService $gestoreEsportazione */
        $gestoreEsportazione = $this->container->get('monitoraggio.esportazione_strutture');
        $estrattore = $gestoreEsportazione->getGestore($struttura);

        return $estrattore->generateResult();
    }

    /**
     * @Route("/estrazione_assi_s3", name="estrazione_assi_s3")
     */
    public function estrazioneAsseS3Action(): Response {
        /** @var SpreadsheetFactory */
        $excelService = $this->get('phpoffice.spreadsheet');
        $spreadSheet = $excelService->getSpreadSheet();
        $spreadSheet->getProperties()->setCreator("Sfinge 2104-2020")
            ->setLastModifiedBy("Sfinge 2104-2020")
            ->setTitle("Esportazione progetti")
            ->setSubject("")
            ->setDescription("")
            ->setKeywords("")
            ->setCategory("");
        $progettiSheet = $spreadSheet->getActiveSheet();
        $this->progettiSheet($progettiSheet);
        $sediOperativeSheet = $spreadSheet->createSheet();
        $this->sediOperativeSheet($sediOperativeSheet);
        $proponentiSheet = $spreadSheet->createSheet();
        $this->proponentiSheet($proponentiSheet);

        return $excelService->createResponse($spreadSheet, 'estrazione S3.xlsx');
    }

    private function progettiSheet(Worksheet &$sheet): void {
        $sheet->setTitle('Progetti');
        $sheet->fromArray([
            'Programma',
            'Asse',
            'Codici azioni',
            'Descrizioni azioni',
            'Numero',
            'Titolo procedura',
            'Anno atto',
            'Stato intervento',
            'Id richiesta',
            'Protocollo',
            'CUP',
            'Data inizio prevista',
            'Data fine prevista',
            'Data ricezione rendicontazione',
            'Data erogazione',
            'Titolo',
            'Abstract',
            'Ambito specializzazione',
            'Orientamento tematico',
            'Brevetti previsti',
            'Brevetti effettivi',
            'Investimento approvato',
            'Contributo ammesso',
            'Investimento effettivo',
            'Contributo erogato',
            'Ricercatori previsti',
            'Ricercatori effettivi',
        ]);

        $connection = $this->getEm()->getConnection();
        $sql = "select
        tc4.cod_programma as programma,
        assi.descrizione as asse,
        (
            select group_concat(a.codice, ',') from azioni a
                join procedure_operative_azioni as poa on poa.azione_id = a.id
                join procedure_operative as poin on poin.id = poa.procedura_id
                where poin.id = po.id
                group by poin.id
        ) as codici_azioni, 
        
        (
            select group_concat(a2.descrizione, ',') from azioni a2
                join procedure_operative_azioni as poa2 on poa2.azione_id = a2.id
                join procedure_operative as poin2 on poin2.id = poa2.procedura_id
                where poin2.id = po.id
                group by poin2.id
        ) as descrizioni_azioni, 
        
               atti.numero,
        po.titolo as titolo_procedura,
        'NON Presente negli atti' as anno_atto,
        case when i.esito_id is null then 'non ancora valutato' else 
            case i.esito_id when 2 then 'non ammesso'
                when 3 then 'sospeso'
                else case concessione when 0 then 'non finanziato'
                    when 1 then case atti_revoche.tipo_id
                        when 1 then 'Revoca totale'
                    --	when 2 then 
                        when 3 then 'Rinucia'
                        else case when mandato.id is null then 'Finanziato'
                            else 'Concluso'
                        end
                    END
                end
            end
        end as stato_intervento,
        r.id as id_richiesta,
        coalesce(concat(pr.registro_pg, '/', pr.anno_pg, '/', pr.num_pg),r.id) as protocollo,
        i.codice_cup as CUP,
        DATE_FORMAT(i.data_avvio_progetto, '%d/%m/%Y') as data_inizio_prevista, 
        DATE_FORMAT(i.data_termine_progetto, '%d/%m/%Y') as data_fine_prevista,
        DATE_FORMAT(saldo.data_invio, '%d/%m/%Y') as data_ricezione_rendicontazione,
        DATE_FORMAT(mandato.data_mandato, '%d/%m/%Y') as data_erogazione,
        
        r.titolo as titolo,
        r.abstract as abstract,
        sp.descrizione as ambito_specializzazione,
        ot.descrizione as orientamento_tematico,
        cast( brevetti.val_programmato as UNSIGNED) as brevetti_previsti,
        cast( brevetti.valore_realizzato as UNSIGNED) as brevetti_effettivi,
        
        format(coalesce(i.costo_ammesso, 0),2, 'it_IT') as investimento_approvato, -- variazioni.costo_ammesso,
        format(coalesce(i.contributo_ammesso, 0),2, 'it_IT') as contributo_ammesso,
        format(sum(coalesce(pagamenti.importo_rendicontato_ammesso, 0)),2, 'it_IT' ) as investimento_effettivo,
        format(sum(coalesce(mandati.importo_pagato, 0)),2, 'it_IT' ) as contributo_erogato,
        
        cast( ricercatori.val_programmato as UNSIGNED) as ricercatori_previsti,
        cast( ricercatori.valore_realizzato as UNSIGNED) as ricercatori_effettivi        
        from richieste as r
        
        join richieste_protocollo as pr
        on pr.richiesta_id = r.id 
        and pr.data_cancellazione is null
        and pr.tipo = 'FINANZIAMENTO'
        
        join procedure_operative as po
        on po.id = r.procedura_id
        
        join istruttorie_richieste as i
        on i.richiesta_id = r.id
        and i.data_cancellazione is null
        
        join proponenti as mandatario
        on mandatario.richiesta_id = r.id
        and mandatario.mandatario = 1
        and mandatario.data_cancellazione is null
        
        join assi 
        on assi.id = po.asse_id
        
        join atti
        on atti.id =po.atto_id
        
        left join attuazione_controllo_richieste as atc 
        on atc.richiesta_id = r.id
        and atc.data_cancellazione is null
        
        left join programmi_procedure_operative as ppo
        on ppo.procedura_id = po.id
        and ppo.data_cancellazione is null
        
        left join tc4_programma as tc4
        on tc4.id = ppo.programma_id
        
        
        left join priorita_proponenti as pp
        on pp.proponente_id = mandatario.id
        
        left join orientamenti_tematici as ot
        on ot.id = pp.orientamento_tematico_id
        
        left join sistemi_produttivi as sp
        on sp.id = ot.sistemaProduttivo_id
        
        left join pagamenti as saldo
        on saldo.attuazione_controllo_richiesta_id = atc.id
        and saldo.data_cancellazione is null
        and saldo.stato_id = 10
        and saldo.modalita_pagamento_id in (3, 4)
        
        left join mandati_pagamenti as mandato
        on mandato.id = saldo.mandato_pagamento_id
        and mandato.data_cancellazione is null
        
        left join revoche
        on revoche.attuazione_controllo_richiesta_id = atc.id
        and revoche.data_cancellazione is null
        and revoche.chiusura_id is null
        
        left join atti_revoche
        on atti_revoche.id = revoche.atto_revoca_id
        
        left join indicatori_output as brevetti
        on brevetti.richiesta_id=r.id
        and brevetti.data_cancellazione is null
        and brevetti.indicatore_id = 266
        
        left join variazioni_richieste as variazioni
        on variazioni.attuazione_controllo_richiesta_id = atc.id
        and variazioni.data_cancellazione is null
        and variazioni.esito_istruttoria = 1
        and variazioni.data_invio  IN (
            (select MAX(v1.data_invio) 
            from variazioni_richieste v1
            where v1.attuazione_controllo_richiesta_id = atc.id
            and v1.data_cancellazione is null
            and v1.esito_istruttoria = 1	) 
        )
        
        left join indicatori_output as ricercatori
        on ricercatori.richiesta_id=r.id
        and ricercatori.data_cancellazione is null
        and ricercatori.indicatore_id = 28
        
        left join pagamenti
        on pagamenti.attuazione_controllo_richiesta_id = atc.id
        and pagamenti.data_cancellazione is null
        and pagamenti.mandato_pagamento_id is not null
        
        left join mandati_pagamenti as mandati
        on mandati.id = pagamenti.mandato_pagamento_id
        and mandati.data_cancellazione is null
        
        group by r.id;
        ";

        $stmt = $connection->prepare($sql);
        $stmt->execute([]);
        $values = $stmt->getIterator();
        foreach ($values as $idx => $record) {
            $sheet->fromArray($record, null, "A" . ($idx + 2));
        }
    }

    private function sediOperativeSheet(Worksheet &$sheet): void {
        $sheet->setTitle('Sedi operative');
        $sheet->fromArray([
            'Protocollo',
            'Comune',
            'Provincia',
            'Codice ISTAT',
        ]);
        $qb = $this->getEm()->createQueryBuilder();
        $expr = $qb->expr();
        $qb->select(
            "coalesce(concat(p.registro_pg, '/', p.anno_pg, '/', p.num_pg), richiesta.id) as protocollo",
            'c.denominazione as comune',
            'prov.denominazione as provincia',
            'c.codice_completo as codice_istat'
        )
        ->from('RichiesteBundle:SedeOperativa', ' sedeOperativa')
        ->join('sedeOperativa.proponente', 'proponente')
        ->join('proponente.richiesta', 'richiesta')
        ->join('richiesta.procedura', 'procedura')
        ->join('procedura.asse', 'asse', 'WITH', $expr->in('asse.id', [1, 3]))
        ->join('richiesta.richieste_protocollo', 'p')
        ->join('sedeOperativa.sede', 'sede')
        ->join('sede.indirizzo', 'indirizzo')
        ->join('indirizzo.comune', 'c')
        ->join('c.provincia', 'prov')
        ->where("p INSTANCE OF ProtocollazioneBundle\Entity\RichiestaProtocolloFinanziamento");

        $values = $qb->getQuery()->getResult();

        foreach ($values as $idx => $record) {
            $sheet->fromArray($record, null, "A" . ($idx + 2),true);
        }
    }

    private function proponentiSheet(Worksheet &$sheet): void {
        $sheet->setTitle('Proponenti');
        $sheet->fromArray([
            'Protocollo',
            'Denominazione',
            'Codice fiscale',
            'Codice ATECO',
            'Descrizione ATECO',
            'Ruolo',
        ]);
        $qb = $this->getEm()->createQueryBuilder();
        $expr = $qb->expr();
        $qb->select(
            "coalesce(concat(rp.registro_pg, '/', rp.anno_pg, '/', rp.num_pg), r.id) as protocollo",
            's.denominazione',
            's.codice_fiscale',
            'a.codice as codice_ateco',
            'a.descrizione as descrizione_ateco',
            "case pro.mandatario when 1 then 'capofila' else 'partner' end as ruolo_proponente"
        )
        ->from('RichiesteBundle:Proponente', 'pro')
        ->join('pro.richiesta', 'r')
        ->join('r.procedura', 'po')
        ->join('po.asse', 'asse', 'WITH', $expr->in('asse.id', [1, 3]))
        ->join('r.richieste_protocollo', 'rp')
        ->join('pro.soggetto', 's')
        ->leftJoin('s.codice_ateco', 'a')
        ->where("rp INSTANCE OF ProtocollazioneBundle\Entity\RichiestaProtocolloFinanziamento");
        $values = $qb->getQuery()->getResult();

        foreach ($values as $idx => $record) {
            $sheet->fromArray($record, null, "A" . ($idx + 2));
        }
    }

    /**
     * @Route("/estrazione_pagamenti_monitoraggio", name="estrazione_pagamenti_monitoraggio")
     */
    public function estrazionePagamentiAction(): Response{
            /** @var SpreadsheetFactory */
            $excelService = $this->get('phpoffice.spreadsheet');
            $spreadSheet = $excelService->getSpreadSheet();
            $spreadSheet->getProperties()->setCreator("Sfinge 2104-2020")
                ->setLastModifiedBy("Sfinge 2104-2020")
                ->setTitle("Esportazione progetti")
                ->setSubject("")
                ->setDescription("")
                ->setKeywords("")
                ->setCategory("");
            $sheet = $spreadSheet->getActiveSheet();
        $sheet->setTitle('Pagamenti');
        $sheet->fromArray([
            'ID operazione',
            'Protocollo',
            'Titolo progetto',
            'Tipo pagamento',
            'Stato pagamento',
            'ID pagamento',
            'Importo rendicontato',
            'Importo rendicontato ammesso',
            'Numero mandato',
            'Data mandato',
            'Importo pagato',
            'Quota FESR',
            'Quota regione',
            'Quota stato',
            'ID procedura',
            'Titolo procedura',
            'Data istruttoria',
            'Stato istruttoria',
            'Data conclusione progetto',
            'Data fine rendicontazione',
            'Data invio',
            'Tipologia soggetto',
            'Data validazione checklist',
            'Codice pagamento',
            'Importo pagamento monitoraggio',
            'Importo certificato',
            'Anno certificazione',
            'Numero certificazione',
        ]);

        $sql = "select r.id,
        concat(proto_richiesta.`registro_pg`, '/', proto_richiesta.`anno_pg`, '/', proto_richiesta.`num_pg`) as protocollo,
        REPLACE(REPLACE(coalesce(r.titolo, ''), '\r', ''), '\n', '') as titolo_progetto,
        modalita.`descrizione` as tipo_pagamento,
        stato_pag.descrizione as stato_pagamento,
        p.id as id_pagamento,
        p.importo_rendicontato,
        p.importo_rendicontato_ammesso,
        mandati.`numero_mandato`,
        date_format(mandati.`data_mandato`, '%d/%m/%Y') as data_mandato,
        mandati.`importo_pagato`, -- importo_pagato
        mandati.`quota_fesr`,-- quota_fesr
        mandati.`quota_regione`,-- quota_regione
        mandati.`quota_stato`,-- quota_stato
        procedura.id as procedura_id,
        procedura.`titolo` as titolo_procedura,
        coalesce(date_format(p.`data_istruttoria`, '%d/%m/%Y'), '') as data_istruttoria,	-- data_istruttoria
        coalesce(eips.descrizione ,'') as stato_istruttoria,
        CASE WHEN p.data_conclusione_progetto IS NULL THEN '' ELSE DATE_FORMAT(p.data_conclusione_progetto, '%d/%m/%Y') END as data_conclusione_progetto, -- data_conclusione_progetto
        coalesce(date_format(p.`data_fine_rendicontazione`, '%d/%m/%Y'), '') as data_fine_rendicontazione, -- data_fine_rendicontazione
        coalesce(date_format(p.`data_invio`, '%d/%m/%Y'), '') as data_invio, -- data_invio
        coalesce(i.tipologia_soggetto, '') as tipologia_soggetto,
        coalesce(date_format(vcp.`data_validazione`, '%d/%m/%Y'), '') as data_validazione_checklist,
        coalesce(rp.`codice`, '') as codice_pagamento,
        coalesce(rp.`importo`, '') as importo_pag_monitoraggio,
        coalesce(p.`importo_certificato`, '') as importo_certificato,
        coalesce(certificazioni.`anno`, '') as anno_certificazione,
        coalesce(certificazioni.numero, '') as numero_certificazione
    from pagamenti as p
    
    join `modalita_pagamento` as modalita
    on modalita.id = p.`modalita_pagamento_id`
    
    join stati as stato_pag
    on stato_pag.id = p.`stato_id`
    
    left join `mandati_pagamenti` as mandati
    on mandati.id = p.`mandato_pagamento_id`
    and mandati.data_cancellazione is null
    
    join `attuazione_controllo_richieste` as atc
    on atc.id = p.`attuazione_controllo_richiesta_id` 
    and atc.data_cancellazione is null
    
    join richieste as r
    on r.id = atc.richiesta_id 
    and r.data_cancellazione is null
    
    join `procedure_operative` as procedura
    on procedura.id = r.procedura_id
    
    join `istruttorie_richieste` as i
    on i.`richiesta_id` = r.id
    and i.data_cancellazione is null
    
    join `richieste_protocollo` as proto_richiesta
    on proto_richiesta.`richiesta_id` = r.id 
    and proto_richiesta.tipo = 'FINANZIAMENTO' 
    and proto_richiesta.data_cancellazione is null
    
    join esiti_istruttoria_pagamento as eip
    on eip.pagamento_id = p.id
    and eip.data_cancellazione is null
    and eip.stato_id in(37,38)
    
    join stati as eips
    on eips.id = eip.stato_id
    
    left join valutazioni_checklist_pagamenti as vcp
    on vcp.pagamento_id = p.id
    and vcp.data_cancellazione is null
    
    left join checklist_pagamenti as cp
    on cp.id = vcp.checklist_id
    
    left join richieste_pagamenti as rp
    on rp.`pagamento_id` = p.id
    and rp.data_cancellazione is null
    
    left join `certificazioni_pagamenti` as cert_pag
    on cert_pag.pagamento_id = p.id
    
    left join certificazioni
    on certificazioni.id = cert_pag.certificazione_id
    
    where p.data_cancellazione is null and stato_pag.id in (9,10)
    and coalesce(cp.tipologia, '') in ('', 'PRINCIPALE')
    and p.esito_istruttoria = 1
    ;
    ";
    $connection = $this->getEm()->getConnection();
    $stmt = $connection->prepare($sql);
    $stmt->execute([]);
    $values = $stmt->getIterator();
    foreach ($values as $idx => $record) {
        $sheet->fromArray($record, null, "A" . ($idx + 2));
    }

    return $excelService->createResponse($spreadSheet, 'estrazione_pagamenti_'. (new \DateTime())->format('Ymd').'.xlsx');

    }
  
}
