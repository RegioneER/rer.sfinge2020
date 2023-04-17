<?php
namespace SfingeBundle\Form;

use BaseBundle\Form\CommonType;
use RichiesteBundle\Entity\Richiesta;
use RichiesteBundle\Entity\RichiestaRepository;
use SfingeBundle\Entity\Procedura;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class PosizioneImpegnoType extends CommonType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Procedura $procedura */
        $procedura = $options['data']->getPropostaImpegno()->getProcedura();

        $builder
            ->add('ptext', self::text, [
                'label' => 'Testo posizione (PTEXT)',
                'required' => false,
            ])
            ->add('lifnr', self::text, [
                'label' => 'Numero conto del fornitore (LIFNR)',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('zzCup', self::text, [
                'label' => 'Codice unico progetto (ZZCUP)',
                'required' => false,
            ])
            ->add('zzCig', self::text, [
                'label' => 'Codice identificativo gara (ZZCIG)',
                'required' => false,
            ])
            ->add('zzLivello5', self::text, [
                'label' => 'Livello 5 (ZZLIVELLO5)',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('zzCodFormAv', self::text, [
                'label' => 'Codice formattato acquisti verde (ZZ_COD_FORM_AV)',
                'required' => false,
            ])
            ->add('wtges', self::importo, [
                'label' => 'Importo totale riservato in divisa transazione (WTGES)',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('richiesta', self::entity, [
                'class' => Richiesta::class,
                'choice_label' => function (Richiesta $richiesta) {
                    return 'Id: ' . $richiesta->getId() . ' / ' . $richiesta->getProtocollo();
                },
                'placeholder' => '-',
                'query_builder' => function (RichiestaRepository $repo) use ($procedura) {
                    return $repo->createQueryBuilder('r')
                        ->where('r.procedura = :procedura')
                        ->setParameter('procedura', $procedura->getId());
                },
                'constraints' => [
                    new NotBlank(),
                ],
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
            'data_class' => 'IstruttorieBundle\Entity\PosizioneImpegno'
        ]);
        $resolver->setRequired("indietro");
    }
}
