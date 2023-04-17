<?php


namespace Performer\PayERBundle\Service;

use DateTime;
use Exception;

/**
 * Class PayER
 */
class PayER implements PayERInterface
{
    /**
     * @var string
     */
    protected $codicePortale;

    /**
     * @var string
     */
    protected $iv;

    /**
     * @var string
     */
    protected $key;

    /**
     * PayER constructor.
     * @param string $codicePortale
     * @param string $iv
     * @param string $key
     */
    public function __construct(string $codicePortale, string $iv, string $key)
    {
        $this->codicePortale = $codicePortale;
        $this->iv = $iv;
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getCodicePortale(): string
    {
        return $this->codicePortale;
    }

    /**
     * @param array $bufferData
     * @return array[]
     */
    public function getBufferBi(array $bufferData): array
    {
        $bufferDataJson = str_replace("\\/", "/", json_encode($bufferData));
        $bufferDataEncoded = base64_encode($bufferDataJson);
        $bufferBi = [
            'TagOrario' => (new DateTime())->format('YmdHi'),
            'CodicePortale' => $this->codicePortale,
            'BufferDati' => $bufferDataEncoded,
        ];

        $hash = md5($this->iv . $bufferDataJson . $this->key . $bufferBi['TagOrario']);

        $bufferBi['Hash'] = $hash;

        return $bufferBi;
    }

    /**
     * @param array $bufferBi
     * @param string $url
     * @return array
     * @throws Exception
     */
    public function sendRequest(array $bufferBi, string $url): array
    {
        $options = [
            'http' => [
                'header' => "Content-Type: application/json\r\n"
                            . "User-Agent: " . $_SERVER['HTTP_USER_AGENT'] . "\r\n",
                'method' => 'POST',
                'content' => json_encode($bufferBi),
                'ignore_errors' => true,
            ]
        ];
        $context = stream_context_create($options);
        $result = json_decode(file_get_contents($url, false, $context), true);

        if (isset($result['error'])) {
            throw new Exception(sprintf('Il server PayER ha restituito un errore (url %s): %s - %s', $url, $result['status'], $result['error']));
        }

        return $result;
    }
}