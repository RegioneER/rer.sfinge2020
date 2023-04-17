<?php

namespace BaseBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Exception\MissingOptionsException;

/**
 * @Annotation
 */
class ValidaLunghezza extends Length {

	public $max;
    public $min;

    public function __construct($options = null)
    {
        if (null !== $options && !is_array($options)) {
            $options = array(
                'min' => $options,
                'max' => $options,
            );
        }

        parent::__construct($options);

        if (null === $this->min && null === $this->max) {
            throw new MissingOptionsException(sprintf('Either option "min" or "max" must be given for constraint %s', __CLASS__), array('min', 'max'));
        }
    }	
}
