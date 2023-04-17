<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 13/01/16
 * Time: 11:30
 */

namespace BaseBundle\Form\Transformer;


use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

class EntityToIdTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    protected $em;
    /**
     * @var string
     */
    protected $class;
    public function __construct(EntityManager $objectManager, $class)
    {
        $this->em = $objectManager;
        $this->class = $class;
    }
    public function transform($entity)
    {
        if (null === $entity) {
            return;
        }
        return $entity->getId();
    }
    public function reverseTransform($id)
    {
        if (!$id) {
            return null;
        }
        $entity = $this->em
            ->getRepository($this->class)
            ->find($id);
        if (null === $entity) {
            throw new TransformationFailedException();
        }
        return $entity;
    }
}