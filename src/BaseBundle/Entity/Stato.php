<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 09/02/16
 * Time: 13:12
 */

namespace BaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * BaseBundle\Entity\StatoLog
 *
 * @ORM\Entity()
 * @ORM\Table(name="stati")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="tipo", type="string")
 * @ORM\DiscriminatorMap({"GENERICO"="BaseBundle\Entity\Stato", 
 *                        "RICHIESTA"="BaseBundle\Entity\StatoRichiesta", 
 *                        "PAGAMENTO" = "AttuazioneControlloBundle\Entity\StatoPagamento",
 *                        "PROROGA" = "AttuazioneControlloBundle\Entity\StatoProroga",
 *                        "VARIAZIONE" = "AttuazioneControlloBundle\Entity\StatoVariazione",
 *                        "CERTIFICAZIONE" = "CertificazioniBundle\Entity\StatoCertificazione",
 *                        "INTEGRAZIONE"="BaseBundle\Entity\StatoIntegrazione", 
 *                        "PAGAMENTO" = "AttuazioneControlloBundle\Entity\StatoPagamento",
 *                        "ESITO_ISTRUTTORIA_PAGAMENTO"="BaseBundle\Entity\StatoEsitoIstruttoriaPagamento",
 *                        "ESITO_ISTRUTTORIA" = "BaseBundle\Entity\StatoComunicazioneEsitoIstruttoria",
 *                        "RICHIESTA_CHIARIMENTI" = "BaseBundle\Entity\StatoRichiestaChiarimenti",
 *                        "CERTIFICAZIONE_CHIUSURA" = "CertificazioniBundle\Entity\StatoChiusuraCertificazione",
 *                        "COMUNICAZIONE_PROGETTO" = "BaseBundle\Entity\StatoComunicazioneProgetto",
 *                        "COMUNICAZIONE_PAGAMENTO" = "BaseBundle\Entity\StatoComunicazionePagamento"
 * })

 *
 */
class Stato extends EntityTipo
{
    
    /**
     * @ORM\OneToMany(targetEntity="BaseBundle\Entity\VisibilitaStato", mappedBy="stato")
     */
    protected $visibilita;

}