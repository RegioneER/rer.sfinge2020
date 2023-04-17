<?php

namespace BaseBundle\TwigExtension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\TwigTest;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class FunzioniGenericheTwigExtension extends AbstractExtension {

	private $container;

	function __construct(ContainerInterface $container) {
		$this->container = $container;
	}

	function getTests()
	{
		return [
			new TwigTest('instanceof',[$this, 'instanceOf'])
		];
	}

	public function instanceOf($oggetto, string $classe): bool {
		$refl = new \ReflectionObject($oggetto);
		
		return $refl->getName() == $classe;
	}


	public function distinct(iterable $input): array {
        // Non si sa perché funziona
        $array = [];
        \array_push($array, ...$input);

        return \array_unique($array);
    }

    public function getFilters() {
        return [
            new TwigFilter('distinct', [$this,'distinct']),
        ];
    }
}
