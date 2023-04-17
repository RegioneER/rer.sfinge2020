<?php
namespace SfingeBundle\Tests\Controller;

use BaseBundle\Tests\TestWeb;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * @runTestsInSeparateProcesses
 */
class SfingeControllerTest extends TestWeb{
    
    const INDEX_PAGE_URL = '/';
    /**
     * @group functional_test
     */
    public function testIndexAction(){
        $crawler =$this->client->request(Request::METHOD_GET, self::INDEX_PAGE_URL);
        /** @var Response $response */
        $response = $this->client->getResponse();

        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());

        $header = $crawler->filter('body > div.header');
        $this->assertNotEmpty($header);
        $content = $crawler->filter('body > div.page-container');
        $this->assertNotEmpty($content);

        $menu = $content->filter('div.page-sidebar');
        $this->assertNotEmpty($menu);
    }
}