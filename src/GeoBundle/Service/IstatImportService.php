<?php

namespace GeoBundle\Service;

use BaseBundle\Service\BaseService;
use GeoBundle\Entity\GeoComune;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;
use GeoBundle\Entity\GeoProvincia;
use GeoBundle\Model\ComuneIstat;

class IstatImportService extends BaseService {
    const RECORD_PER_RIGA = 23;
    const POS_COD_REGIONE = 0;
    const POS_COD_PROVINCIA = 2;
    const POS_COD_COMUNE = 3;
    const POS_COD_DENOMINAZIONE = 6;
    const POS_COD_CATASTO = 18;
    const POS_COD_COMUNE2016 = 15;
    const POS_CAPOLUOGO = 12;
    const CODICE_ITALIA = '11101';
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var GeoProvincia[]
     */
    private $cacheProvincie = [];

    public function __construct(ContainerInterface $container) {
        parent::__construct($container);
        $this->em = $container->get('doctrine.orm.entity_manager');
    }

    public function importComuni(): array {
        $arrayComuni = $this->container->get('geo.istat_api')->getElencoComuni();
        $comuniIstat = \array_map([ComuneIstat::class, 'fromArray'], $arrayComuni);
        $comuni = \array_map([$this, 'creaComune'], $arrayComuni);
        $comuniNonPresenti = \array_filter($comuni, [$this, 'isComuneNonPresente']);
        //Ingarra
        $connection = $this->em->getConnection();
        try {
            $connection->beginTransaction();
            \array_walk($comuniNonPresenti, [$this, 'ingarraDB']);
            $connection->commit();
        } catch (\Exception $e) {
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }
            throw new \Exception("Errore nel salvataggio delle informazioni", 1, $e);
        }
        // \array_walk($comuniNonPresenti, [$this, 'comuniToScreen']);

        return $comuniNonPresenti;
    }
/*
    private function comuniToScreen(GeoComune $comune){
        \vprintf("%s\t%s\t%s\t%s\t%s\n",[
            $comune->getDenominazione(),
            $comune->getCodice(),
            $comune->getCodiceCompleto(),
            $comune->getCodiceCatastale(),
            $comune->getCapoluogo() ? 'S' : 'N'
        ]);
    }
*/
    protected function creaComune(array $tupla): GeoComune {
        if (self::RECORD_PER_RIGA != \count($tupla)) {
            throw new \Exception('Tupla non completa');
        }
        $codProvincia = \substr($tupla[self::POS_COD_COMUNE2016],0, strlen($tupla[self::POS_COD_COMUNE2016]) - 3);
        $codProvincia = \str_pad($codProvincia, 3, '0',STR_PAD_LEFT);
        $codComune = \substr($tupla[self::POS_COD_COMUNE2016],-3);
        $provincia = $this->getProvincia($codProvincia);

        $comune = new GeoComune();
        $comune->setProvincia($provincia);
        $comune->setCodice($tupla[self::POS_COD_COMUNE]);
        $comune->setDenominazione($tupla[self::POS_COD_DENOMINAZIONE]);
        $comune->setCapoluogo(true == $tupla[self::POS_CAPOLUOGO]);
        $comune->setCodiceCatastale($tupla[self::POS_COD_CATASTO]);
        $comune->setDataIstituzione(new \DateTime('1800-01-01'));
        $comune->setCodiceCompleto(
                self::CODICE_ITALIA .
                $tupla[self::POS_COD_REGIONE] .
                \str_pad($tupla[self::POS_COD_COMUNE2016], 6, '0',STR_PAD_LEFT));

        return $comune;
    }

    private function getProvincia(string $codice): GeoProvincia {
        if (\array_key_exists($codice, $this->cacheProvincie)) {
            return $this->cacheProvincie[$codice];
        }

        $provincia = $this->em->getRepository(GeoProvincia::class)->findOneBy([
            'codice' => $codice,
        ]);
        if (\is_null($provincia)) {
            throw new \Exception('Provincia non trovata a sistema');
        }

        $this->cacheProvincie[$codice] = $provincia;

        return $provincia;
    }

    protected function isComuneNonPresente(GeoComune $comune): bool {
        // $codice = $comune->getCodice();
        $repo = $this->em->getRepository(GeoComune::class);
        $comunePresente = true == $repo->findOneBy([
            // 'codiceCatastale' => $codice,
            'codice_completo' => $comune->getCodiceCompleto(),
            'cessato' => 0,
        ]);

        return !$comunePresente;
    }

    protected function ingarraDB(GeoComune $comune): void {
        $this->em->persist($comune);
        $this->em->flush($comune);
    }
}
