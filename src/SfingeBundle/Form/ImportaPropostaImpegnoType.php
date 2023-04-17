<?php
namespace SfingeBundle\Form;

use AttuazioneControlloBundle\Entity\ModalitaPagamentoProcedura;
use BaseBundle\Form\CommonType;
use IstruttorieBundle\Entity\PropostaImpegno;
use SfingeBundle\Entity\Procedura;
use SfingeBundle\Entity\ProceduraRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotNull;

class ImportaPropostaImpegnoType extends CommonType
{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('procedura', self::entity, [
            'class' => Procedura::class,
            'required' => true,
            'placeholder' => '-',
            'query_builder' => function (ProceduraRepository $repo) {
                $qb = $repo->createQueryBuilder('p')
                ->where('p INSTANCE OF SfingeBundle:Bando OR p INSTANCE OF SfingeBundle:ProceduraPA');

                return $qb;
            },
            'choice_label' => function (Procedura $bando) {
                return $bando->getId() . ' - ' . $bando->getTitolo();
            },
        ]);

        $builder->add('propostaImpegno', 'Symfony\Component\Form\Extension\Core\Type\FileType', [
            'label' => 'File',
            'required'=> true,
            'constraints' => [
                new NotNull(),
                new File([
                    'maxSize' => '1024k',
                    'mimeTypes' => [
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    ],
                    'mimeTypesMessage' => 'Caricare un file .xls oppure .xlsx',
                ])
            ],
            'estensione' => 'Excel (.xls .xlsx)',
            'mapped' => false,
            ]);

        $builder->add('submit', self::salva_indietro, [
            'url' => $options['indietro'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'data_class' => PropostaImpegno::class,
        ]);

        $resolver->setRequired('indietro');
    }
}
