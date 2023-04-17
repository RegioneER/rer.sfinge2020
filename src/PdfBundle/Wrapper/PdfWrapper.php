<?php

namespace PdfBundle\Wrapper;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;


class PdfWrapper
{
    /**
     * @var string
     */
    private $basePath;

    /**
     * @var string[]
     */
    private $options;

    private $html;

    protected $container;

	private $pageSize = 'A4'; /* 'letter', 'legal', 'A4 */
	
	private $pageOrientation = 'portrait' ; /* 'portrait' or 'landscape' */
	
    /**
     * @param string   $basePath
     * @param string[] $options
     */
    public function __construct($container, $basePath, array $options = array())
    {
        $this->basePath = $basePath;
        $this->options  = $options;
        $this->html = null;

        $this->container = $container;
    }

    /**
     * Load a twig template
     */
    public function load($twig, $data)
    {
        $this->html = $this->container->get('templating')->render($twig, $data);
    }

    /**
     * Force Download the pdf file
     */
    public function download($filename)
    {
        if ( ! $this->html) {
            throw new \Exception("You need to use a twig template before call the download method.");
        }
        
        $this->streamHtml($this->html, $filename, array('isHtml5ParserEnabled' => true));
    }

    /**
     * Get odf binary data
     */
    public function binaryData()
    {
        if ( ! $this->html) {
            throw new \Exception("You need to use a twig template before call the download method.");
        }

        return $this->getPdf($this->html);
    }

    /**
     * Renders a pdf document and streams it to the browser.
     *
     * @param string    $html         The html sourcecode to render
     * @param string    $filename     The name of the docuemtn
     * @param string[]  $options      The rendering options (see dompdf docs)
     * @param bool|true $replacePaths Appends the basepath to file links
     *
     * @throws \Exception
     */
    public function streamHtml($html, $filename, array $options = array(), $replacePaths = true)
    {
        if ($replacePaths) {
            // $html = $this->replaceBasePath($html);
        }

        $pdf = $this->createDompdf();				
        $pdf->setOptions($this->createOptions($options));
        $pdf->loadHtml($html);
        $pdf->render();
        $pdf->stream($filename);
    }

    /**
     * Renders a pdf document and return the binary content.
     *
     * @param string    $html         The html sourcecode to render
     * @param array     $options      The rendering options (see dompdf docs)
     * @param bool|true $replacePaths Appends the basepath to file links
     *
     * @throws \Exception
     *
     * @return string
     */
    public function getPdf($html, array $options = array(), $replacePaths = true)
    {
        if ($replacePaths) {
            // $html = $this->replaceBasePath($html);
        }

        $pdf = $this->createDompdf();
        $pdf->setOptions($this->createOptions($options));
        $pdf->loadHtml($html);
        $pdf->render();

        return $pdf->output();
    }

    /**
     * Replaces relative paths with absolute paths.
     *
     * @param string $html The html sourcecode
     *
     * @return string Modified html sourcecode
     */
    private function replaceBasePath($html)
    {
        $pattern = '#<([^>]* )(src|href)=([\'"])(?![A-z]*:)([^"]*)([\'"])#';
        $replace = '<$1$2=$3'.$this->basePath.'$4$5';

        return preg_replace($pattern, $replace, $html);
    }

    /**
     * Creates a new Dompdf instance.
     *
     * @return Dompdf
     */
    public function createDompdf()
    {
        $dom_pdf = new Dompdf();
		$dom_pdf->setPaper($this->pageSize, $this->pageOrientation);
		return $dom_pdf;
    }

    /**
     * Creates a a new Option instance.
     *
     * @param string[] $options An array of dompdf options
     *
     * @return Options
     */
    public function createOptions(array $options = array())
    {
        return new Options(array_merge($this->options, $options));
    }
	
	function getPageSize() {
		return $this->pageSize;
	}

	function getPageOrientation() {
		return $this->pageOrientation;
	}

	/* 'letter', 'legal', 'A4' */
	function setPageSize($pageSize) {
		$legal_value = array('letter', 'legal', 'A4', 'A3');
		$this->pageSize = in_array($pageSize, $legal_value) ? $pageSize : 'A4';
	}

	/* 'portrait' or 'landscape' */
	function setPageOrientation($pageOrientation) {
		$legal_value = array('portrait', 'landscape');
		$this->pageOrientation = in_array($pageOrientation, $legal_value) ? $pageOrientation : 'portrait';
	}

	/**
	 * @param $html
	 */
	function setHtml($html) {
		$this->html = $html;
	}
}
