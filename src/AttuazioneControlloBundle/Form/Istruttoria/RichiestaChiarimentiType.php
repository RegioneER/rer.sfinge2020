<?php

namespace AttuazioneControlloBundle\Form\Istruttoria;

use BaseBundle\Form\CommonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotNull;
use AttuazioneControlloBundle\Entity\Istruttoria\AllegatoRichiestaChiarimento;
use DocumentoBundle\Entity\DocumentoFile;
use Doctrine\ORM\EntityManagerInterface;
use AttuazioneControlloBundle\Entity\Istruttoria\RichiestaChiarimento;
use DocumentoBundle\Entity\TipologiaDocumento;

class RichiestaChiarimentiType extends CommonType
{

	public function buildForm(FormBuilderInterface $builder, array $options)
    {
		/** @var RichiestaChiarimento $data */
		$data = $builder->getData();
		$builder->add('testo', self::textarea, array(
				'label' => 'Testo',
				'required' => false	,
				'attr' => array('rows' => 6)
			)
		);
		
		$builder->add('testoEmail', self::textarea, array(
				'label' => 'Testo email',
				'required' => true,
				'constraints' => array(new NotNull()),
				'attr' => array('rows' => 6)
			)
		);

		$builder->add('pulsanti', self::salva_invia_indietro, array(
			"url" => $options["url_indietro"],  
			"label" => false, 
			"disabled" => false));				
		
	}
	
	/**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => RichiestaChiarimento::class
        ));
		
		$resolver->setRequired("url_indietro");
    }
}
