<?php

namespace AttuazioneControlloBundle\Service\Istruttoria;

use AttuazioneControlloBundle\Entity\IstruttoriaOggettoPagamento;
use AttuazioneControlloBundle\Entity\Pagamento;
use AttuazioneControlloBundle\Entity\RendicontazioneProceduraConfig;
use Exception;
use RichiesteBundle\Utility\EsitoValidazione;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\HttpFoundation\Response;


class GestoreIncrementoOccupazionaleBase extends AGestoreIncrementoOccupazionale
{
    /**
     * @param Procedura $procedura
     * @return RendicontazioneProceduraConfig
     */
    public function getRendicontazioneProceduraConfig(Procedura $procedura)
    {
        $rendicontazioneProceduraConfig = $procedura->getRendicontazioneProceduraConfig();
        if (is_null($rendicontazioneProceduraConfig)) {
            $rendicontazioneProceduraConfig = new RendicontazioneProceduraConfig();
        }

        return $rendicontazioneProceduraConfig;
    }

    /**
     * @param Procedura $procedura
     * @return mixed
     */
    public function getAvvisoSezioneIncrementoOccupazionale(Procedura $procedura)
    {
        $rendicontazioneProceduraConfig = $this->getRendicontazioneProceduraConfig($procedura);
        return $rendicontazioneProceduraConfig->getAvvisoSezioneIncrementoOccupazionale();
    }
    
    /**
     * @param Pagamento $pagamento
     * @return mixed|Response
     */
    public function dettaglioIncrementoOccupazionale(Pagamento $pagamento, $twig = null)
    {
        //$options['disabled'] = $this->get("gestore_istruttoria_pagamenti")->getGestore($pagamento->getProcedura())->isRichiestaDisabilitata();
        $options['disabled'] = $pagamento->isRichiestaDisabilitata();
        $options['url_indietro'] = $this->generateUrl('riepilogo_istruttoria_pagamento', ['id_pagamento' => $pagamento->getId()]);

        $richiesta = $pagamento->getRichiesta();
        $form = $this->createForm('AttuazioneControlloBundle\Form\IncrementoOccupazionale\ConfermaIncrementoOccupazionaleType', $pagamento, $options);

        $this->container->get('pagina')->aggiungiElementoBreadcrumb('Elenco pagamenti', $this->generateUrl('elenco_istruttoria_pagamenti'));
        $this->container->get('pagina')->aggiungiElementoBreadcrumb('Riepilogo pagamento', $this->generateUrl('riepilogo_istruttoria_pagamento', ['id_pagamento' => $pagamento->getId()]));
        $this->container->get('pagina')->aggiungiElementoBreadcrumb('Conferma incremento occupazionale');

        $options['form'] = $form->createView();
        $options['pagamento'] = $pagamento;
        $options['dataInizio'] = $richiesta->getIstruttoria()->getDataAvvioProgetto();
        $options['dataFine'] = $richiesta->getIstruttoria()->getDataTermineProgetto();

        $caricamentoNuoviDipendenti = $this->getCaricamentoNuoviDipendenti($richiesta->getProcedura());
        $options['caricamentoNuoviDipendenti'] = $caricamentoNuoviDipendenti;
        
        $avviso = $this->getAvvisoSezioneIncrementoOccupazionale($richiesta->getProcedura());
        // Nel caso in cui nell'avviso siano stati messi i placeholder per le date vado a fare un replace.
        $avviso = str_replace('DATA_DA', $options['dataInizio']->format('d/m/Y'), $avviso);
        $avviso = str_replace('DATA_A', $options['dataFine']->format('d/m/Y'), $avviso);
        $options['avviso'] = $avviso;

        // ISTRUTTORIA
        $istruttoria = $pagamento->getIstruttoriaIncrementoOccupazionale();
        if (is_null($istruttoria)) {
            $istruttoria = new IstruttoriaOggettoPagamento();
            $pagamento->setIstruttoriaIncrementoOccupazionale($istruttoria);
        }

        $form_istruttoria = $this->createForm("AttuazioneControlloBundle\Form\Istruttoria\IstruttoriaOggettoPagamentoType", $istruttoria, ['url_indietro' => $options['url_indietro']]);
        $request = $this->getCurrentRequest();

        if ($request->isMethod('POST')) {
            $form_istruttoria->handleRequest($request);

            if ($form_istruttoria->isValid()) {
                try {
                    $em = $this->getEm();
                    $em->persist($pagamento);
                    $em->flush();
                    return $this->addSuccesRedirect('Istruttoria sull\'incremento occupazionale salvata correttamente',
                        'dettaglio_incremento_occupazionale_istruttoria', ['id_pagamento' => $pagamento->getId()]);
                } catch (Exception $e) {
                    $this->addError('Errore nel salvataggio delle informazioni. Si prega di riprovare o contattare l\'assistenza.');
                }
            }
        }
        
        $options['form_istruttoria'] = $form_istruttoria->createView();
        $options['config'] = $this->getRendicontazioneProceduraConfig($pagamento->getProcedura());
        // FINE ISTRUTTORIA
         if(is_null($twig)) {
            $twig = '@AttuazioneControllo/Pagamenti/IncrementoOccupazionale/confermaIncrementoOccupazionale.twig';
        }
        
        return $this->render($twig, $options);
    }

    /**
     * @param Pagamento $pagamento
     * @return mixed|EsitoValidazione
     */
    public function validaIncrementoOccupazionale(Pagamento $pagamento)
    {
        $esito = new EsitoValidazione(true);

        $rendicontazioneProceduraConfig = $this->getRendicontazioneProceduraConfig($pagamento->getProcedura());
        if ($rendicontazioneProceduraConfig->getIncrementoOccupazionale() && !$pagamento->getModalitaPagamento()->isAnticipo()) {
            $istruttoria = $pagamento->getIstruttoriaIncrementoOccupazionale();
            if (is_null($istruttoria) || ($istruttoria->isIncompleta())) {
                $esito->setEsito(false);
                $esito->addMessaggioSezione("Istruttoria incremento occupazionale incompleta");
            }
            
            if (!is_null($istruttoria) && $istruttoria->isIntegrazione()) {
                $esito->setMessaggio('Integrazione');
            }
        }

        return $esito;
    }

    /**
     * @param Procedura $procedura
     * @return mixed
     */
    public function getCaricamentoNuoviDipendenti(Procedura $procedura)
    {
        $rendicontazioneProceduraConfig = $this->getRendicontazioneProceduraConfig($procedura);
        return $rendicontazioneProceduraConfig->getIncrementoOccupazionaleNuoviDipendenti();
    }
}
