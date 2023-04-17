<?php
namespace MonitoraggioBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
/**
 * @runTestsInSeparateProcesses
 */
class MonitoraggioEsportazioneControllerTest extends WebTestCase
{
    const PASSWORD = 'password';
    const USERNAME = 'DMCVCN81A15G273T';

    const URL_ELENCO_ESPORTAZIONI = '/monitoraggio/esportazioni/elenco';
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client $client
     */
    protected $client;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->client = self::createClient();
        $this->doLogin();
    }

    protected function doLogin()
    {
        $crawler = $this->client->request('GET', '/login');
        $form = $crawler->selectButton('Accedi')->form(array(
            '_username' => self::USERNAME,
            '_password' => self::PASSWORD,
        ));
        $this->client->submit($form);
    }

    /**
     * dataProvider urlProvider
     */
    public function testPageElencoIsSuccessful()
    {
        $this->client->request('GET', self::URL_ELENCO_ESPORTAZIONI);
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    public function testPresenzaPulsanteEsportazione()
    {
        $crawler = $this->client->request('GET', self::URL_ELENCO_ESPORTAZIONI);
        $bottone = $crawler->selectButton('elenco_esportazioni[submit]');
        $this->assertTrue($bottone->html() == 'Crea nuovo invio');
    }
/*
    public function testPulsanteEsportazioneAbilitato()
    {
        $this->setFindEsportazioneInCorso(false);
        $crawler = $this->client->request('GET', self::URL_ELENCO_ESPORTAZIONI);
        $bottone = $crawler->selectButton('elenco_esportazioni[submit]');
        $this->assertNull($bottone->attr('disabled'));
    }


    protected function setFindEsportazioneInCorso($value)
    {
        $monitoraggioEsportazioneRepository = $this->createMock(\MonitoraggioBundle\Repository\MonitoraggioEsportazioneRepository::class);
        $monitoraggioEsportazioneRepository->expects($this->any())
            ->method('findEsportazioneInCorso')
            ->willReturn($value);

        $esportazione = $this->createMock(\Doctrine\ORM\EntityManager::class);
        $esportazione->expects($this->any())
            ->method('getRepository')
            ->willReturn($monitoraggioEsportazioneRepository);
    }
*/
    /**
     * @return array
     */
    public function urlProvider()
    {
        return array(
            array('/monitoraggio/esportazioni/elenco'),
        );
    }

}