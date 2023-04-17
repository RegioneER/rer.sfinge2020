<?php


namespace Performer\PayERBundle\Type;


use Performer\PayERBundle\Entity\AcquistoMarcaDaBollo;
use Performer\PayERBundle\Entity\RichiestaAcquistoMarcaDaBollo;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;

/**
 * Class RichiestaAcquistoMarcaDaBolloType
 */
class RichiestaAcquistoMarcaDaBolloType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('identificativoVersante', TextType::class, [
                'required' => true
            ])
            ->add('denominazioneVersante', TextType::class, [
                'required' => true
            ])
            ->add('emailVersante', TextType::class, [
                'required' => false
            ])
            ->add('acquistoMarcaDaBollos', EntityType::class, [
                'class' => AcquistoMarcaDaBollo::class,
                'required' => true,
                'multiple' => true,
                'by_reference' => false,
                'constraints' => [
                    new Count(['min' => 1])
                ],
            ])
            ->add('urlRitorno', UrlType::class, [
                'required' => true,
                'mapped' => false
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => RichiestaAcquistoMarcaDaBollo::class,
            'method' => 'get',
            'csrf_protection' => false,
        ]);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'payer_ebollo_richiesta_acquisto_marca_da_bollo';
    }

}