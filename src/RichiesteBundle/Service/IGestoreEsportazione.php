<?php

namespace RichiesteBundle\Service;

interface IGestoreEsportazione {

	public function estrazioneRichieste($opzioni = array());
	
	/**
     * @return \PHPExcel_Writer_IWriter
     * @throws \Exception
     */
	public function getReportVariazioni();
}
