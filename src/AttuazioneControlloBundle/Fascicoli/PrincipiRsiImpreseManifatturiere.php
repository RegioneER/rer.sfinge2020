<?php
namespace AttuazioneControlloBundle\Fascicoli;

use Exception;
use FascicoloBundle\Entity\IstanzaPagina;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class PrincipiRsiImpreseManifatturiere
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    protected $fascicoloService;

    /**
     * @param $container
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->fascicoloService = $this->container->get("fascicolo.istanza");
    }

    /**
     * @param IstanzaPagina $istanzaPagina
     * @return ConstraintViolationList
     */
    public function validaSceltaInnovazioneSostenibile(IstanzaPagina $istanzaPagina): ConstraintViolationList
    {
        $violazioni = new ConstraintViolationList();
        $istanzaFascicolo = $istanzaPagina->getIstanzaFascicolo();

        $campo = $this->fascicoloService->getOne($istanzaFascicolo, 'principi_rsi_imprese_manifatturiere.indice_rsi.principi_rsi_sezione_8.principi_rsi_sotto_pagina_8_1.campo_8_1_1', true);
        if (!empty($campo) && count($campo) > 2) {
            $violazioni->add(new ConstraintViolation('Campo “8.1 La scelta di adottare processi di innovazione sostenibile è motivata soprattutto da“ selezionare al massimo 2 opzioni.', null, [], $istanzaPagina, '', null));
        }

        $campo_scelta = $this->container->get("fascicolo.istanza")->getOne($istanzaPagina->getIstanzaFascicolo(), "principi_rsi_imprese_manifatturiere.indice_rsi.principi_rsi_sezione_8.principi_rsi_sotto_pagina_8_1.campo_8_1_1", true);
        $campo_altro = $this->container->get("fascicolo.istanza")->get($istanzaPagina->getIstanzaFascicolo(), "principi_rsi_imprese_manifatturiere.indice_rsi.principi_rsi_sezione_8.principi_rsi_sotto_pagina_8_1.campo_8_1_2");

        // Validazione campo "Altro (specificare)"
        $processiDiInnovazione = false;
        if(is_null($campo_scelta)) {
            $campo_scelta = array();
        }
        if(in_array('5', $campo_scelta)) {
            $processiDiInnovazione = true;
        }

        if ($processiDiInnovazione  && is_null($campo_altro)) {
            $violazioni->add(new ConstraintViolation('Campo “8.1 La scelta di adottare processi di innovazione sostenibile è motivata soprattutto da“ in caso di selezione della voce "Altro (specificare)" è obbligatorio fornire una descrizioni nel campo "Altro (specificare)".', null, [], $istanzaPagina, "", null));
        } elseif (!$processiDiInnovazione && !is_null($campo_altro)) {
            $violazioni->add(new ConstraintViolation('Campo “8.1 La scelta di adottare processi di innovazione sostenibile è motivata soprattutto da“ non compilare la sezione "Altro (specificare)" senza aver selezionato la voce "Altro (specificare)".', null, [], $istanzaPagina, "", null));
        }

        return $violazioni;
    }
}
