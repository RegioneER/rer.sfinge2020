<?php
namespace SfingeBundle\Form;

use BaseBundle\Form\CommonType;
use SfingeBundle\Entity\Bando;
use SfingeBundle\Entity\Procedura;
use SfingeBundle\Entity\ProceduraRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class PropostaImpegnoType extends CommonType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('procedura', self::entity, [
                'class' => Procedura::class,
                'choice_label' => function (Bando $bando) {
                    return $bando->getId() . ' - ' . $bando->getTitolo();
                },
                'placeholder' => '-',
                'query_builder' => function (ProceduraRepository $repo) {
                    return $repo->createQueryBuilder('p')
                        ->where('p INSTANCE OF SfingeBundle:Bando OR p INSTANCE OF SfingeBundle:ProceduraPA');
                },
            ])
            ->add('bukrs', self::text, [
                'label' => 'Società (BUKRS)',
                'required' => false,
                'constraints' => [
                    new NotBlank(),
                ],
                'attr'=> ['placeholder' => 'RER',],
            ])
            ->add('bldat', self::birthday, [
                'widget' => 'single_text',
                'input' => 'datetime',
                'format' => 'dd/MM/yyyy',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
                'label' => 'Data doc nel documento (BLDAT)'
            ])
            ->add('ktext', self::text, [
                'label' => 'Testo testata documento (KTEXT)',
                'required' => false,
            ])
            ->add('budat', self::birthday, [
                'widget' => 'single_text',
                'input' => 'datetime',
                'format' => 'dd/MM/yyyy',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
                'label' => 'Data di registrazione nel documento (BUDAT)'
            ])
            ->add('zz_protocollo', self::text, [
                'label' => 'Protocollo (ZZPROTOCOLLO)',
                'required' => false,
            ])
            ->add('zz_tipo_doc', self::text, [
                'label' => 'Tipo documento (ZZTIPODOC)',
                'required' => false,
            ])
            ->add('zz_contr_imp', self::text, [
                'label' => 'Tipo gestione impegno (ZZCONTR_IMP)',
                'required' => false,
            ])
            ->add('zz_fipos', self::text, [
                'label' => 'Capitolo (ZZFIPOS)',
                'required' => false,
            ])
		    ->add('pulsanti',self::salva_indietro, [
                'url'=>$options['indietro']
            ]);
    }
    
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'IstruttorieBundle\Entity\PropostaImpegno'
        ]);
        $resolver->setRequired("indietro");
    }
}
