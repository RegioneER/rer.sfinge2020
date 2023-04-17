<?php
/**
 * Created by PhpStorm.
 * User: rstronati
 * Date: 13/01/16
 * Time: 11:27
 */

namespace BaseBundle\Form;


use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use BaseBundle\Form\Transformer\EntityToIdTransformer;

class EntityHiddenType extends AbstractType
{
    /**
     * @var EntityManager
     */
    protected $entityManager;
    public function __construct(EntityManager $objectManager)
    {
        $this->entityManager = $objectManager;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new EntityToIdTransformer($this->entityManager, $options['class']);
        $builder->addModelTransformer($transformer);
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(array('class'))
            ->setDefaults(array(
                'data_class' => null,
                'invalid_message' => 'The entity does not exist.',
            ))
        ;
    }
    public function getParent()
    {
        return 'Symfony\Component\Form\Extension\Core\Type\HiddenType';
    }
    // public function getName()
    // {
    //     return 'entity_hidden';
    // }
}