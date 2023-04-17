<?php

namespace SoggettoBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Exception;

/**
 * FormaGiuridicaRepository
 */
class FormaGiuridicaRepository extends EntityRepository {
    const formeGiuridicheComuniUnioni = ['2.4.30', '2.4.50'];
    const formeGiuridicheUniversita = ['2.6.20', '2.7.11'];
    const formeGiuridicheProfessionisti = ['1.1.10', '1.1.20', '1.1.30', '1.1.40'];
    const formeGiuridicheProfessionistiSenzaAgricoliEAutonomi = ['1.1.20', '1.1.30'];
    const formeGiuridichePersonaFisica = ['9.9.99'];
    const formeGiuridicheAzienda = [
        '1.2.10',
        '1.2.20',
        '1.2.30',
        '1.2.40',
        '1.2.50',
        '1.3.10',
        '1.3.20',
        '1.3.30',
        '1.3.40',
        '1.3.50',
        '1.4.10',
        '1.4.20',
        '1.4.30',
        '1.4.40',
        '1.5.10',
        '1.5.20',
        '1.5.30',
        '1.5.40',
        '1.6.10',
        '1.6.20',
        '1.6.30',
        '1.7.10',
        '1.7.20',
        '1.7.30',
        '1.7.40',
        '1.7.50',
        '1.7.90',
        '1.8.10',
        '1.8.20',
        '1.8.30',
        '1.8.90',
        '1.9.00',
    ];

    public function dammiRepoTipoByFormaGiuridica(string $tipo, string $formaGiuridica = null): array {
        return $this->dammiRepoTipoByFormaGiuridicaQb($tipo, $formaGiuridica)->getQuery()->getResult();
    }

    public function dammiRepoTipoByFormaGiuridicaQb(string $tipo, string $formaGiuridica = null): QueryBuilder {
        switch ($tipo) {
            case Soggetto::AZIENDA:
                return $this->aziende($formaGiuridica);
            case Soggetto::PROFESSIONISTA:
                return $this->professionisti($formaGiuridica);
            case Soggetto::COMUNE:
                return $this->comuniUnioni($formaGiuridica);
            case Soggetto::UNIVERSITA:
                return $this->universitaCentriRicerca($formaGiuridica);
            case Soggetto::ALTRI:
                return $this->altro($formaGiuridica);
            default:
                return $this->altro();
        }
    }

   
    public function comuniUnioni(string $formaGiuridica = null): QueryBuilder {
        $codiciFormeGiuridiche = array_merge(self::formeGiuridicheComuniUnioni, [$formaGiuridica]);
        return $this->createQueryBuilder('e')
                    ->where('e.codice IN(:codici)')
                    ->orderBy('e.codice', 'ASC')
                    ->setParameter('codici', $codiciFormeGiuridiche);
    }

   
    public function universitaCentriRicerca(string $formaGiuridica = null): QueryBuilder {
        $codiciFormeGiuridiche = array_merge(self::formeGiuridicheUniversita, [$formaGiuridica]);

        return $this->createQueryBuilder('e')
                    ->where('e.codice IN(:codici)')
                    ->orderBy('e.codice', 'ASC')
                    ->setParameter('codici', $codiciFormeGiuridiche);
    }

   
    public function professionisti(string $formaGiuridica = null) {
        $codiciFormeGiuridiche = array_merge(self::formeGiuridicheProfessionisti, [$formaGiuridica]);

        return $this->createQueryBuilder('e')
                    ->where('e.codice IN(:codici)')
                    ->orderBy('e.codice', 'ASC')
                    ->setParameter('codici', $codiciFormeGiuridiche);
    }

    public function aziende(string $formaGiuridica = null) {
        $codiciFormeGiuridiche = array_merge(self::formeGiuridicheAzienda, [$formaGiuridica]);
        return $this->createQueryBuilder('e')
        ->where('e.codice IN(:codici)')
        ->orderBy('e.codice', 'ASC')
        ->setParameter('codici', $codiciFormeGiuridiche);
    }

    public function altro(string $formaGiuridica = null) {
        $codiciFormeGiuridiche = array_diff(array_merge(self::formeGiuridicheProfessionisti, self::formeGiuridicheUniversita, self::formeGiuridicheComuniUnioni, self::formeGiuridicheAzienda), [$formaGiuridica]);

        return $this->createQueryBuilder('e')
        ->where('e.codice NOT IN(:codici)')
        ->orderBy('e.codice', 'ASC')
        ->setParameter('codici', $codiciFormeGiuridiche);
    }

    /**
     * @param $param
     *
     * @return array
     */
    public function ricercaByCodiceDescrizione($param) {
        $q = $this->getEntityManager()->createQuery("SELECT fg FROM SoggettoBundle:FormaGiuridica fg WHERE fg.codice LIKE '%{$param}%' OR fg.descrizione LIKE '%{$param}%' ORDER BY fg.codice");

        return $q->getResult();
    }

    /**
     * @return array
     */
    public function getFormeGiuridiche() {
        $q = $this->getEntityManager()->createQuery("SELECT fg FROM SoggettoBundle:FormaGiuridica fg ORDER BY fg.descrizione");

        return $q->getResult();
    }

    public function ricercaDaDescrizioneAdrier($descrizione) {
        $descr = strtolower(str_replace('U\'', 'ù', str_replace('O\'', 'ò', str_replace('I\'', 'ì', str_replace('E\'', 'è', str_replace('A\'', 'à', $descrizione))))));

        $q = $this->getEntityManager()
            ->createQuery("
                    SELECT fg FROM SoggettoBundle:FormaGiuridica fg 
                    WHERE LOWER(fg.descrizione) = '{$descr}'")
            ->setMaxResults(1)
            ->getOneOrNullResult();
        return $q;
    }

    /**
     * @param $formaGiuridica
     *
     * @return bool
     * @throws Exception
     */
    public function isPersonaFisica(FormaGiuridica $formaGiuridica) {
        if (!is_null($formaGiuridica)) {
            $tmp = $formaGiuridica->getCodice();
            switch ($formaGiuridica->getCodice()) {
                case "1.1.10":    //Imprenditore individuale agricolo
                    return true;
                    break;
                case "1.1.20":    //Imprenditore individuale non agricolo
                    return true;
                    break;
                case "1.1.30":    //Libero professionista
                    return true;
                    break;
                case "1.1.40":    //Lavoratore autonomo
                    return true;
                    break;
                default:
                    return false;
            }
        } else {
            throw new Exception("Forma giuridica non valida");
        }
    }

    public function supportsClass($class) {
        return 'SoggettoBundle\Entity\FormaGiuridica' === $class;
    }
}
