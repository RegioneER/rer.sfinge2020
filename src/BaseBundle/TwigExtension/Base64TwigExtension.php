<?php
namespace BaseBundle\TwigExtension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class Base64TwigExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('base64_encode', 'base64_encode'),
            new TwigFilter('base64_decode', [$this, 'base64Decode'])
        ];
    }

    public function base64Decode($input): string
    {
        if ( base64_encode(base64_decode($input, true)) === $input){
            return base64_decode($input);
        } else {
            return $input;
        }
    }

    public function getName(): string
    {
        return "base64";
    }
}
