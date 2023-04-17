<?php

namespace RichiesteBundle\Service;

use RichiesteBundle\Utility\EsitoValidazione;
use Symfony\Component\HttpFoundation\Response;
use RichiesteBundle\Entity\ObiettivoRealizzativo;

interface IGestoreObiettiviRealizzativi {
    const STD_TWIG_ELENCO_OBIETTIVI = 'RichiesteBundle:Richieste:ObiettiviRealizzativi/elenco.html.twig';
    const STD_TWIG_FORM_OBIETTIVO = 'RichiesteBundle:Richieste:ObiettiviRealizzativi/form_obiettivo.html.twig';
    const ROUTE_ELENCO_OBIETTIVI = 'elenco_obiettivi_realizzativi_richiesta';
    const ROUTE_NUOVO_OBIETTIVO = 'nuovo_obiettivo_realizzativo';
    const ROUTE_MODIFICA_OBIETTIVO = 'modifica_obiettivo_realizzativo';
    const ROUTE_ELIMINA_OBIETTIVO = 'elimina_obiettivo_realizzativo';

    public function valida(): EsitoValidazione;

    public function elencoObiettivi(): Response;

    public function nuovoObiettivo(): Response;

    public function modificaObiettivo(ObiettivoRealizzativo $obiettivo): Response;

    public function eliminaObiettivo(ObiettivoRealizzativo $obiettivo): Response;
}
