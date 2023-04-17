<?php

namespace MonitoraggioBundle\Utils;

class StringWrapper {
    /**
     * @var string
     */
    private $value;

    public function __construct(string $value) {
        $this->value = $value;
    }

    public function __toString(): string {
        return $this->value;
    }
}
