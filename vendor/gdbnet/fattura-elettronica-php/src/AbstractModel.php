<?php

namespace Gdbnet\FatturaElettronica;

abstract class AbstractModel
{
    /**
     * @param array $array
     * @return array
     */
    protected function cleanArray(array $array): array
    {
        foreach ($array as $key => $item) {
            if (is_array($item)) {
                $array[$key] = $this->cleanArray($item);
            }
            if (empty($array[$key])) {
                unset($array[$key]);
            }
        }

        return $array;
    }
}