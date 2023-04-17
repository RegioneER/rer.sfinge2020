<?php
namespace MonitoraggioBundle\Validator\Validators;



class FN06_FN07_055Validator extends AbstractValidator
{
    public function validate($value, \Symfony\Component\Validator\Constraint $constraint)
    {

        if (!\in_array($value->getTavolaProtocollo(), array('FN06', 'FN07')) || !$this->checkDuplicateError($value, $constraint)) {
            return;
        }

        $dql_ammessi = 'select sum(case pagamenti_ammessi.tipologia_pag_amm '
                . "when 'P' then pagamenti_ammessi.importo_pag_amm "
                . "when 'P-TR' then pagamenti_ammessi.importo_pag_amm "
                . "else 0 end ) "
                . ' *  '
                . " case when pagamenti_ammessi_cancellati.id is null then 1 else 0 end   risultato "
            . 'from MonitoraggioBundle:FN07PagamentiAmmessi pagamenti_ammessi '
            . 'join pagamenti_ammessi.monitoraggio_configurazione_esportazioni_tavola tavola_pagamenti_ammessi '
            . 'join tavola_pagamenti_ammessi.monitoraggio_configurazione_esportazione configurazione_pagamenti_amessi '
            . 'join MonitoraggioBundle:MonitoraggioConfigurazioneEsportazioneRichiesta configurazione_richiesta with configurazione_richiesta = configurazione_pagamenti_amessi ' 
            . 'join configurazione_richiesta.richiesta richiesta_pagamenti_ammessi '
            . 'left join MonitoraggioBundle:FN07PagamentiAmmessi pagamenti_ammessi_cancellati with '
                . 'pagamenti_ammessi_cancellati.cod_locale_progetto = pagamenti_ammessi.cod_locale_progetto '
                . 'and pagamenti_ammessi_cancellati.cod_pagamento = pagamenti_ammessi.cod_pagamento '
                . 'and pagamenti_ammessi_cancellati.tipologia_pag = pagamenti_ammessi.tipologia_pag '
                . 'and pagamenti_ammessi_cancellati.data_pagamento = pagamenti_ammessi.data_pagamento '
                . 'and pagamenti_ammessi_cancellati.tc4_programma = pagamenti_ammessi.tc4_programma '
                . 'and pagamenti_ammessi_cancellati.tc36_livello_gerarchico = pagamenti_ammessi.tc36_livello_gerarchico '
                . 'and pagamenti_ammessi_cancellati.data_pag_amm = pagamenti_ammessi.data_pag_amm '
                . 'and pagamenti_ammessi_cancellati.tipologia_pag_amm = pagamenti_ammessi.tipologia_pag_amm '
                . "and pagamenti_ammessi_cancellati.flg_cancellazione = 'S' "
            . 'where richiesta_pagamenti_ammessi = :richiesta and pagamenti_ammessi.flg_cancellazione is null ';

            $res_pagamenti_ammessi = $this->em
            ->createQuery($dql_ammessi)
            ->setParameter('richiesta', $value->getMonitoraggioConfigurazioneEsportazione()->getRichiesta())
            ->getSingleResult();

        $dql_pagamenti = 'select sum ( case pagamenti.tipologia_pag '
                . "when 'P' then pagamenti.importo_pag "
                . "when 'P-TR' then pagamenti.importo_pag "
                . "else 0 end * case when pagamenti_cancellati.id is null then 1 else 0 end"
                . ' ) risultato '
            . 'from MonitoraggioBundle:FN06Pagamenti pagamenti '
            . 'join pagamenti.monitoraggio_configurazione_esportazioni_tavola tavola_pagamenti '
            . 'join tavola_pagamenti.monitoraggio_configurazione_esportazione configurazione_pagamenti '
            . 'join MonitoraggioBundle:MonitoraggioConfigurazioneEsportazioneRichiesta configurazione_richiesta with configurazione_richiesta = configurazione_pagamenti ' 
            . 'join configurazione_richiesta.richiesta richiesta_pagamenti '
            . 'left join MonitoraggioBundle:FN06Pagamenti pagamenti_cancellati with '
                . 'pagamenti_cancellati.cod_locale_progetto = pagamenti.cod_locale_progetto '
                . 'and pagamenti_cancellati.cod_pagamento = pagamenti.cod_pagamento '
                . 'and pagamenti_cancellati.tipologia_pag = pagamenti.tipologia_pag '
                . 'and pagamenti_cancellati.data_pagamento = pagamenti.data_pagamento '
                . "and pagamenti_cancellati.flg_cancellazione = 'S' "
            . ' where richiesta_pagamenti = :richiesta and pagamenti.flg_cancellazione is null ';
            $res_pagamenti = $this->em
            ->createQuery($dql_pagamenti)
            ->setParameter('richiesta', $value->getMonitoraggioConfigurazioneEsportazione()->getRichiesta())
            ->getSingleResult();

        $pagamenti = is_null($res_pagamenti['risultato']) ? 0 : $res_pagamenti['risultato'];
        $pagamenti_ammessi = is_null($res_pagamenti_ammessi['risultato']) ? 0 : $res_pagamenti_ammessi['risultato'];
        

        if ($pagamenti_ammessi > $pagamenti ) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}