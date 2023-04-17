<?php
namespace RichiesteBundle\Form;

use Doctrine\ORM\EntityRepository;
use RichiesteBundle\Entity\AmbitoTematicoS3Proponente;
use RichiesteBundle\Entity\DescrittoreAmbitoTematicoS3Proponente;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;

class DescrittoreAmbitoTematicoS3ProponenteType extends RichiestaType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var DescrittoreAmbitoTematicoS3Proponente $descrittoreAmbitoTematicoS3Proponente */
        $descrittoreAmbitoTematicoS3Proponente = $builder->getData();
        /** @var AmbitoTematicoS3Proponente $ambitoTematicoS3Proponente */
        $ambitoTematicoS3Proponente = $descrittoreAmbitoTematicoS3Proponente->getAmbitoTematicoS3Proponente();

        $builder->add('descrittore', self::entity, [
            'class' => 'SfingeBundle\Entity\DescrittoreAmbitoTematicoS3',
            'choice_label' => 'descrizione',
            'required' => true,
            'label' => "Selezionare un descrittore",
            'disabled' => $options['disabled'],
            'placeholder' => '-',
            'constraints' => [new NotNull()],
            'query_builder' => function (EntityRepository $er) use ($ambitoTematicoS3Proponente) {
                return $er->createQueryBuilder("d")
                    ->where("d.ambito_tematico_s3 = :ambitoTematicoS3")
                    ->setParameter("ambitoTematicoS3", $ambitoTematicoS3Proponente->getAmbitoTematicoS3())
                    ->orderBy("d.descrizione", "ASC");
            },
        ]);

        if ($options['has_descrizione']) {
            $builder->add('descrizione', self::textarea, [
                "label" => "Descrizione",
                "required" => true,
                'disabled' => $options['disabled'],
                'attr' => ['style' => 'width: 500px', 'rows' => '5'],
                'constraints' => [new NotNull()],
            ]);
        }

        $builder->add('pulsanti', self::salva_indietro, [
            'url' => $options['url_indietro'],
            'disabled' => false
        ]);
    }

    /**
     * @param OptionsResolver $resolver
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => "RichiesteBundle\Entity\DescrittoreAmbitoTematicoS3Proponente",
            'validation_groups' => function ($form) {
                /** @var DescrittoreAmbitoTematicoS3Proponente $data */
                $data = $form->getData();
                if ($data->getAmbitoTematicoS3Proponente()->getProponente()->getRichiesta()->getProcedura()->getAmbitiTematiciS3DescrizioneDescrittori()) {
                    return ["Default"];
                }
            },
        ]);
        $resolver->setRequired("disabled");
        $resolver->setRequired("url_indietro");
        $resolver->setRequired("has_descrizione");
    }
}
