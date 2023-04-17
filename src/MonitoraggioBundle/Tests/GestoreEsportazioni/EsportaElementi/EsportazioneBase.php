<?php

namespace MonitoraggioBundle\Tests\GestoreEsportazioni\EsportaElementi;

use BaseBundle\Tests\Service\TestBaseService;
use Doctrine\Common\Persistence\ObjectRepository;
use MonitoraggioBundle\Exception\EsportazioneException;

class EsportazioneBase extends TestBaseService {
    /**
     * @var Esporta
     */
    protected $esporta;

    protected function esportazioneNonNecessaria(ObjectRepository $repo) {
        $this->em->method('getRepository')->willReturn($repo);

        $this->expectException(EsportazioneException::class);

        $res = $this->esporta->execute($this->richiesta, $this->tavola, true);

        return $res;
    }

    protected function importazioneConInputNonValido() {
        $this->expectException(EsportazioneException::class);
        $res = $this->esporta->importa([]);

        return $res;
    }

    protected function createMockFindOneBy($repositoryClass, $object):ObjectRepository{
        $repository = $this->createMock($repositoryClass);
        $repository->method('findOneBy')->willReturn($object);

        return $repository;
    }
}
