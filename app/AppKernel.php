<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel {

    public function registerBundles() {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            //prefabbricati
            new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            //schema
            new BaseBundle\BaseBundle(),
            new SfingeBundle\SfingeBundle(),
            new PaginaBundle\PaginaBundle(),
            new NotizieBundle\NotizieBundle(),
            new AnagraficheBundle\AnagraficheBundle(),
            new GeoBundle\GeoBundle(),
            new SoggettoBundle\SoggettoBundle(),
            new UtenteBundle\UtenteBundle(),
            new FascicoloBundle\FascicoloBundle(),
            new MessaggiBundle\MessaggiBundle(),
            new DocumentoBundle\DocumentoBundle(),
            new ProtocollazioneBundle\ProtocollazioneBundle(),
            new Liuggio\ExcelBundle\LiuggioExcelBundle(),
            new CipeBundle\CipeBundle(),
            new RichiesteBundle\RichiesteBundle(),
            new PdfBundle\PdfBundle(),
            new SegnalazioniBundle\SegnalazioniBundle(),
            new IstruttorieBundle\IstruttorieBundle(),
			new AuditBundle\AuditBundle(),
            new FaqBundle\FaqBundle(),
            new AttuazioneControlloBundle\AttuazioneControlloBundle(),
            new CertificazioniBundle\CertificazioniBundle(),
            new MonitoraggioBundle\MonitoraggioBundle(),
            new Exercise\HTMLPurifierBundle\ExerciseHTMLPurifierBundle(),
            new FunzioniServizioBundle\FunzioniServizioBundle(),
            new Performer\PayERBundle\PerformerPayERBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'), true)) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader) {
        $loader->load($this->getRootDir() . '/config/config_' . $this->getEnvironment() . '.yml');
    }

}
