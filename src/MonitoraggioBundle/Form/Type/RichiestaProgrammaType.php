<?php
namespace MonitoraggioBundle\Form\Type;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormInterface;
use AttuazioneControlloBundle\Entity\RichiestaProgramma;

class RichiestaProgrammaType extends CommonType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $data = $builder->getData();
        $disabled = $options['disabled'];

        $builder->add('tc4_programma', self::entity, array(
            'class' => 'MonitoraggioBundle\Entity\TC4Programma',
            'disabled' => $disabled,
            'required' => !$disabled,
            'label' => 'Programma',
        ));

        $builder->add('stato', self::choice, array(
            'disabled' => $disabled,
            'required' => !$disabled,
            'label' => 'Stato',
            'choices_as_values' => true,
            'choices' => \array_flip(RichiestaProgramma::getStati()),
        ));

        $builder->add('tc14_specifica_stato', self::entity, array(
            'class' => 'MonitoraggioBundle\Entity\TC14SpecificaStato',
            'disabled' => $disabled,
            'required' => false,
            'label' => 'Specifica stato',
            'query_builder' => function (\Doctrine\ORM\EntityRepository $er) use ($data) {
                if( is_null($data)){
                    return $er->createQueryBuilder('u');
                }
                
                //else
                $richiesta = $data->getRichiesta();
                return $er->createQueryBuilder('u')
                    ->select('distinct u')
                    ->join('u.procedure', 'programma_procedura')
                    ->join('programma_procedura.procedura', 'procedura_operativa')
                    ->leftJoin('u.richieste_programmi', 'ap04')
                    ->leftJoin('ap04.richiesta', 'richiesta', 'with', 'richiesta = :richiesta')
                    ->leftJoin('richiesta.mon_programmi', 'programmi_associati')
                    ->leftJoin('programmi_associati.tc4_programma', 'programmi')
                    ->where('procedura_operativa = :procedura')
                    ->andWhere('programmi_associati.id is null or programmi <> u or programmi = :programma')
                    ->setParameters(array(
                    'procedura' => $richiesta->getProcedura(),
                    'richiesta' => $richiesta,
                    'programma' => $data->getTc4Programma(),
                ));
            }
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults(array(
            'data_class' => 'AttuazioneControlloBundle\Entity\RichiestaProgramma',
            'empty_data' => function (FormInterface $form) {
                $richiesta = $form->getParent()->getData()->getOwner();
                return new RichiestaProgramma($richiesta);
           },
        ));
    }
}