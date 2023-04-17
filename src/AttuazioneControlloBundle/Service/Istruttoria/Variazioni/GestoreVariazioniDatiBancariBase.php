<?php

namespace AttuazioneControlloBundle\Service\Istruttoria\Variazioni;

use AttuazioneControlloBundle\Service\Istruttoria\AGestoreVariazioni;
use AttuazioneControlloBundle\Entity\VariazioneDatiBancari;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use RichiesteBundle\Entity\Proponente;
use AttuazioneControlloBundle\Entity\VariazioneDatiBancariProponente;

class GestoreVariazioniDatiBancariBase extends AGestoreVariazioni implements IGestoreVariazioniDatiBancari {
    /**
     * @var VariazioneDatiBancari
     */
    protected $variazione;

    public function __construct(VariazioneDatiBancari $variazione, ContainerInterface $container) {
        $this->variazione = $variazione;
        $this->container = $container;
    }

    public function dettaglioDatiBancari(Proponente $proponente): Response {
        $varazioneDatiProponente = $this->variazione->getDatiBancari()
        ->filter(
            function (VariazioneDatiBancariProponente $variazioneDatiBancari) use ($proponente) {
                $proponenteVariazione = $variazioneDatiBancari->getProponente();
                return $proponente == $proponenteVariazione;
            })
        ->first();

        return $this->render('AttuazioneControlloBundle:Istruttoria\Variazioni:datiBancariProponente.html.twig', [
            'variazione_dati' => $varazioneDatiProponente,
            'variazione' => $this->variazione,
        ]);
    }

    protected function applicaVariazione(): void {
        foreach ($this->variazione->getDatiBancari() as $variazioneDati) {
            $variazioneDati->applica();
        }
    }
}
