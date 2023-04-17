<?php
namespace ProtocollazioneBundle\Form\Entity;

use BaseBundle\Service\AttributiRicerca;
use ProtocollazioneBundle\Entity\Log;
use ProtocollazioneBundle\Form\RicercaLogType;

class RicercaLog extends AttributiRicerca
{
    private $richiesta_protocollo_id;
    private $app_function_target;

    public function getType(): string
    {
        return RicercaLogType::class;
    }

    public function getNomeRepository(): string
    {
        return Log::class;
    }

    public function getNomeMetodoRepository(): string
    {
        return "getLogByRichiestaProtocolloId";
    }

    public function getNumeroElementiPerPagina()
    {
        return null;
    }

    public function getNomeParametroPagina(): string
    {
        return "page";
    }

    /**
     * @return mixed
     */
    public function getRichiestaProtocolloId()
    {
        return $this->richiesta_protocollo_id;
    }

    /**
     * @param mixed $richiesta_protocollo_id
     */
    public function setRichiestaProtocolloId($richiesta_protocollo_id): void
    {
        $this->richiesta_protocollo_id = $richiesta_protocollo_id;
    }

    /**
     * @return mixed
     */
    public function getAppFunctionTarget()
    {
        return $this->app_function_target;
    }

    /**
     * @param mixed $app_function_target
     */
    public function setAppFunctionTarget($app_function_target): void
    {
        $this->app_function_target = $app_function_target;
    }
}
