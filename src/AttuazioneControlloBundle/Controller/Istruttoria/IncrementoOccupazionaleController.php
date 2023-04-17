<?php

namespace AttuazioneControlloBundle\Controller\Istruttoria;

use AttuazioneControlloBundle\Entity\Pagamento;
use AttuazioneControlloBundle\Service\Istruttoria\IGestoreIncrementoOccupazionale;
use BaseBundle\Controller\BaseController;
use Exception;
use PaginaBundle\Annotations\Menuitem;
use PaginaBundle\Annotations\PaginaInfo;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SfingeBundle\Entity\Procedura;


/**
 * @Route("/istruttoria/pagamenti/incremento_occupazionale")
 */
class IncrementoOccupazionaleController extends BaseController
{
    /**
     * @param int $id_pagamento
     * @return Pagamento|null
     */
    protected function getPagamento(int $id_pagamento): ?Pagamento
    {
        $pagamento = $this->getEm()->getRepository('AttuazioneControlloBundle\Entity\Pagamento')->find($id_pagamento);
        return $pagamento;
    }

    /**
     * @param Procedura|null $procedura
     * @return IGestoreIncrementoOccupazionale
     * @throws Exception
     */
    protected function getGestoreIncrementoOccupazionaleIstruttoria(?Procedura $procedura = null): IGestoreIncrementoOccupazionale
    {
        return $this->get("gestore_incremento_occupazionale_istruttoria")->getGestore($procedura);
    }
    
    /**
     * @param int $id_pagamento
     * 
     * @Route("/{id_pagamento}/dettaglio_incremento_occupazionale_istruttoria", name="dettaglio_incremento_occupazionale_istruttoria")
     * @PaginaInfo(titolo="Incremento occupazionale",sottoTitolo="Conferma incremento occupazionale")
     * @Menuitem(menuAttivo="elencoIstruttoriaPagamenti")
     * 
     * @return mixed
     * @throws Exception
     */
    public function dettaglioIncrementoOccupazionaleIstruttoriaAction(int $id_pagamento)
    {
        $pagamento = $this->getPagamento($id_pagamento);
        return $this->getGestoreIncrementoOccupazionaleIstruttoria($pagamento->getProcedura())->dettaglioIncrementoOccupazionale($pagamento);
    }



    
    
    
    
}
