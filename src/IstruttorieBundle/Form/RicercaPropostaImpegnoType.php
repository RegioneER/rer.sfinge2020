<?php
namespace IstruttorieBundle\Form;

use BaseBundle\Form\CommonType;
use SfingeBundle\Entity\Bando;
use SfingeBundle\Entity\Procedura;
use SfingeBundle\Entity\ProceduraRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RicercaPropostaImpegnoType extends CommonType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
	public function buildForm(FormBuilderInterface $builder, array $options)
    {
		parent::buildForm($builder, $options);

        $builder->add('procedura', self::entity, [
            'class' => Procedura::class,
            'choice_label' => function (Bando $bando) {
                return $bando->getId() . ' - ' . $bando->getTitolo();
            },
            'placeholder' => '-',
            'query_builder' => function (ProceduraRepository $repo) {
                return $repo->createQueryBuilder('p')
                    ->where('p INSTANCE OF SfingeBundle:Bando OR p INSTANCE OF SfingeBundle:ProceduraPA');
            },
        ]);
    }

    /**
     * @param OptionsResolver $resolver
     * @return void
     */
	public function configureOptions(OptionsResolver $resolver)
    {
		$resolver->setDefaults([
            'data_class' => 'IstruttorieBundle\Form\Entity\RicercaPropostaImpegno',
        ]);
	}
}
