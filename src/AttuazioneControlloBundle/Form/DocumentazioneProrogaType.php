<?php
/**
 * @author lfontana
 */

namespace AttuazioneControlloBundle\Form;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityManager;

class DocumentazioneProrogaType extends CommonType
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('documento', 'DocumentoBundle\Form\Type\DocumentoFileType', array(
            'lista_tipi' => $options['tipi'],
            'label' => false,
        ))
        ->add('submit', self::salva_indietro, array(
            'url' => $options['url_indietro'],
            'label_salva' => 'Carica',
        ));
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AttuazioneControlloBundle\Entity\DocumentoProroga',
            'tipi' => $this->em->getRepository('DocumentoBundle:TipologiaDocumento')->findByCodice(\AttuazioneControlloBundle\Entity\DocumentoProroga::CODICE_DOCUMENTO),
        ))
        ->setRequired('url_indietro');
    }

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }
}
