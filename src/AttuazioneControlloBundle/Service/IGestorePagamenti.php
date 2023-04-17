<?php

namespace AttuazioneControlloBundle\Service;

use AttuazioneControlloBundle\Entity\Pagamento;
use AttuazioneControlloBundle\Entity\RichiestaImpegni;
use RichiesteBundle\Entity\IndicatoreOutput;
use DocumentoBundle\Entity\DocumentoFile;
use AttuazioneControlloBundle\Entity\DocumentoImpegno;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use RichiesteBundle\Utility\EsitoValidazione;

interface IGestorePagamenti {
    public function elencoPagamenti($id_richiesta);

    public function getModalitaPagamento($richiesta);

    public function aggiungiPagamento($id_richiesta);

    public function dettaglioPagamento($id_pagamento, $twig = null);

    public function eliminaPagamento($id_pagamento);

    public function gestioneDocumentiPagamento($id_pagamento);

    public function gestioneDocumentiDropzonePagamento(Pagamento $pagamento): ?Response;

    public function caricaDocumentoDropzone(Request $request, Pagamento $pagamento): array;

    public function concatChunksDocumentoDropzone(Request $request, $id_pagamento): array;

    public function validaDocumentiDropzone(Pagamento $pagamento): EsitoValidazione;

    public function validaPagamento($id_pagamento);

    public function invalidaPagamento($id_pagamento);

    public function inviaPagamento($id_richiesta);

    public function gestioneBarraAvanzamento($pagamento);

    public function validaDatiGenerali($pagamento);

    public function validaGiustificativi($pagamento);

    public function validaDocumenti($pagamento);

    public function eliminaDocumentoPagamento($id_documento_pagamento);

    public function datiGeneraliPagamento($id_pagamento);

    public function calcolaImportoRichiestoIniziale($pagamento);

    public function recuperaPagamento($id_pagamento);

    public function gestioneIndicatori(Pagamento $pagamento): Response;

    public function validaMonitoraggioIndicatori(Pagamento $pagamento);

    public function gestioneFasiProcedurali(Pagamento $pagamento);

    public function validaMonitoraggioFasiProcedurali(Pagamento $pagamento): EsitoValidazione;

    public function gestioneImpegni(Pagamento $pagamento);

    public function gestioneFormImpegno(Pagamento $pagamento, RichiestaImpegni $impegno = null);

    public function validaImpegni(Pagamento $pagamento);

    public function eliminaImpegno(Pagamento $pagamento, $id_impegno);

    public function gestioneProceduraAggiudicazione(Pagamento $pagamento): Response;

    public function validaProceduraAggiudicazione(Pagamento $pagamento): EsitoValidazione;

    public function gestioneModificaProceduraAggiudicazione(Pagamento $pagamento, $id_procedura_aggiudicazione);

    public function gestioneEliminaProceduraAggiudicazione(Pagamento $pagamento, $id_procedura_aggiudicazione);

    public function gestioneSingoloIndicatore(Pagamento $pagamento, IndicatoreOutput $indicatore): Response;

    public function eliminaDocumentoIndicatoreOutput(Pagamento $pagamento, IndicatoreOutput $indicatore, DocumentoFile $documento): Response;

	public function eliminaDocumentoImpegno(Pagamento $pagamento, DocumentoImpegno $documento): Response;
}
