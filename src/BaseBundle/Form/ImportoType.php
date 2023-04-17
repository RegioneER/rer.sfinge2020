<?php

namespace BaseBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use BaseBundle\Form\Transformer\ImportoToStringTransformer;

class ImportoType extends MoneyType {
	
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addViewTransformer(new ImportoToStringTransformer(
                $options['scale'],
                $options['grouping'],
                null,
                $options['divisor']
            ))
        ;
    }
}
