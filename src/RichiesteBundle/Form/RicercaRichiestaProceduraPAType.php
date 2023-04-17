<?php
namespace RichiesteBundle\Form;
use Symfony\Component\Form\FormBuilderInterface;
class RicercaRichiestaProceduraPAType extends RicercaRichiestaType 
{
    public function buildForm(FormBuilderInterface $builder, array $options) {
		parent::buildForm($builder, $options);
		
		$ricerca = $builder->getData();	/** @var \RichiesteBundle\Ricerca\RicercaProcedurePA $ricerca */
		$procedure = $ricerca->getQueryRicercaProcedura($this->em, $options);

        $builder->add('procedura', self::entity, array(
			'choices' => $procedure,
			'class' => 'SfingeBundle:ProceduraPA',
			'placeholder' => '-',
			'required' => false,
		));
    }
}
