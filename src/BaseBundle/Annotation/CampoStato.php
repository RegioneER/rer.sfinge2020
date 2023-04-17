<?php

namespace BaseBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 * @Attributes({
 *   @Attribute("proprieta", type = "string")
 * })
 */

final class CampoStato extends Annotation
{
	public $proprieta = "codice";
}