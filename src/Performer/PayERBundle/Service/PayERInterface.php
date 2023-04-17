<?php


namespace Performer\PayERBundle\Service;

/**
 * Interface PayERInterface
 */
interface PayERInterface
{
    public function getCodicePortale(): string;
    public function getBufferBi(array $bufferData): array;
    public function sendRequest(array $bufferBi, string $url): array;
}