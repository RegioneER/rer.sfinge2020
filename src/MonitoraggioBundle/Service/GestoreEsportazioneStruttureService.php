<?php

namespace MonitoraggioBundle\Service;

use BaseBundle\Service\BaseService;
use MonitoraggioBundle\GestoriEsportazione\IEstrattoreStruttura;

class GestoreEsportazioneStruttureService extends BaseService {
    const STRUTTURE_NAMESPACE = '\MonitoraggioBundle\GestoriEsportazione\EstrazioneStrutture';
    const STRUTTURE_ESPORTABILI = [
        'AP00',
        'AP01',
        'AP02',
        'AP03',
        'AP04',
        'AP05',
        'AP06',
        'FN00',
        'FN01',
        'FN02',
        'FN03',
        'FN04',
        'FN05',
        'FN06',
        'FN07',
        'FN08',
        'FN09',
        'FN10',
        'IN00',
        'IN01',
        'PR00',
        'PR01',
        'SC00',
        'PG00',
        'PA00',
        'PA01',
    ];

    public function getGestore(string $struttura): IEstrattoreStruttura {
        $refl = new \ReflectionClass(self::STRUTTURE_NAMESPACE . "\\$struttura");
        /** @var IEstrattoreStruttura $estrattore */
        $estrattore = $refl->newInstance($this->container);

        return $estrattore;
    }

    public function getStruttureEsportabili(): array {
        return \array_filter(self::STRUTTURE_ESPORTABILI, function (string $struttura): bool {
            return \class_exists(self::STRUTTURE_NAMESPACE . '\\' . $struttura);
        });
    }
}
