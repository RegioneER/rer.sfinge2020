<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 13/01/16
 * Time: 11:30
 */

namespace DocumentoBundle\Form\Transformer;


use BaseBundle\Service\FunzioniUtili;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class CfTransformer implements DataTransformerInterface
{
    /**
     * @var FunzioniUtili
     */
    protected $funzioniUtili;
    /**
     * @var string
     */
    public function __construct(FunzioniUtili $funzioniUtili)
    {
        $this->funzioniUtili = $funzioniUtili;
    }
    public function transform($cf)
    {
        if(is_array($cf)) {
            $cf = implode(',', $cf);
        }
        return $this->funzioniUtili->encid($cf);
    }
    public function reverseTransform($cf_codificato)
    {
        return $this->funzioniUtili->decid($cf_codificato);
    }
}