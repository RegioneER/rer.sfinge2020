<?php
/**
 * Created by PhpStorm.
 * User: alessiofavilli
 * Date: 06/06/17
 * Time: 11:42
 */

namespace MonitoraggioBundle\Entity;

use BaseBundle\Entity\EntityLoggabileCancellabile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use AttuazioneControlloBundle\Entity\ModalitaPagamento;
use BaseBundle\Entity\Id;

/**
 * @ORM\Entity(repositoryClass="MonitoraggioBundle\Repository\TC39CausalePagamentoRepository")
 * @ORM\Table(name="tc39_causale_pagamento")
 */
class TC39CausalePagamento extends EntityLoggabileCancellabile {
    use Id;
    const ANTICIPO = 'ANT';
    const PAGAMENTO_INTERMEDIO = 'INT';
    const SALDO = 'SLD';
    const ALTRO = 'ALT';
    const SNA = 'SNA';

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     * @Assert\NotNull
     * @Assert\Length(max=3, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     * @var string
     */
    protected $causale_pagamento;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     * @var string
     */
    protected $descrizione_causale_pagamento;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Length(max=255, maxMessage="Il campo non può superare i {{ limit }} caratteri")
     * @var string
     */
    protected $tipologia_pagamento;

    /**
     * @return string
     */
    public function getCausalePagamento() {
        return $this->causale_pagamento;
    }

    /**
     * @param string $causale_pagamento
     * @return self
     */
    public function setCausalePagamento($causale_pagamento) {
        $this->causale_pagamento = $causale_pagamento;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescrizioneCausalePagamento() {
        return $this->descrizione_causale_pagamento;
    }

    /**
     * @param string $descrizione_causale_pagamento
     * @return self
     */
    public function setDescrizioneCausalePagamento($descrizione_causale_pagamento) {
        $this->descrizione_causale_pagamento = $descrizione_causale_pagamento;
        return $this;
    }

    /**
     * @return string
     */
    public function getTipologiaPagamento() {
        return $this->tipologia_pagamento;
    }

    /**
     * @param string $tipologia_pagamento
     * @return self
     */
    public function setTipologiaPagamento($tipologia_pagamento) {
        $this->tipologia_pagamento = $tipologia_pagamento;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->causale_pagamento . ' - ' . $this->descrizione_causale_pagamento;
    }

    /**
     * @param ModalitaPagamento $modalita
     * @return string
     */
    public static function CodiceDaMandatoPagamento(ModalitaPagamento $modalita) {
        $codiceModalita = $modalita->getCodice();
        $mapping = [
            ModalitaPagamento::SAL => self::PAGAMENTO_INTERMEDIO,
            ModalitaPagamento::PRIMO_SAL => self::PAGAMENTO_INTERMEDIO,
            ModalitaPagamento::SECONDO_SAL => self::PAGAMENTO_INTERMEDIO,
            ModalitaPagamento::TERZO_SAL => self::PAGAMENTO_INTERMEDIO,
            ModalitaPagamento::QUARTO_SAL => self::PAGAMENTO_INTERMEDIO,
            ModalitaPagamento::QUINTO_SAL => self::PAGAMENTO_INTERMEDIO,

            ModalitaPagamento::ANTICIPO => self::ANTICIPO,

            ModalitaPagamento::SALDO_FINALE => self::SALDO,

            ModalitaPagamento::UNICA_SOLUZIONE => self::SALDO,

            ModalitaPagamento::TRASFERIMENTO => self::ALTRO,
        ];

        return $mapping[$codiceModalita];
    }
}
