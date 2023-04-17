<?php

namespace FascicoloBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;

class AdvancedTextType extends TextareaType implements DataTransformerInterface {

    private $purifier;

    /**
     * Constructor.
     *
     * @param \HTMLPurifier $purifier
     */
    public function __construct(\HTMLPurifier $purifier)
    {
        $this->purifier = $purifier;
    }

    /**
     * @see Symfony\Component\Form\DataTransformerInterface::transform()
     */
    public function transform($value)
    {
        return $value;
    }

    /**
     * @see Symfony\Component\Form\DataTransformerInterface::reverseTransform()
     */
    public function reverseTransform($value)
    {
        return $this->purifier->purify($value);
    }

    public function getBlockPrefix() {
        return 'advanced_text';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent() {
        return TextareaType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer($this);
        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver) {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'language' => 'it',
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options) {
        parent::buildView($view, $form, $options);

        $view->vars = array_merge($view->vars, [
            'language' => $options['language'],
        ]);
    }
}
