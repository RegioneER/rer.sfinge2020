<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace CipeBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use CipeBundle\Entity\WsGeneraCup;

/**
 * Servizio di invocazione del web-service WsGeneraCup del CIPE
 * @see http://cb.schema31.it/cb/issue/177623
 *
 * @author gaetanoborgosano
 */
class WsGeneraCupService {
	
	/**
	 * @var ContainerInterface
	 */
	protected $container;
	protected function getContainer() { return $this->container; }
	protected function setContainer($container) { $this->container = $container; }
	protected function getParameter($name) { $this->getContainer()->getParameter($name); }
	
	protected $ws_genera_cup_url = "";
	function getWs_genera_cup_url() { return $this->ws_genera_cup_url; }
	function setWs_genera_cup_url($ws_genera_cup_url) { $this->ws_genera_cup_url = $ws_genera_cup_url; }

	/**
	 * @var WsGeneraCup 
	 */
	protected $WsGeneraCup;
	function getWsGeneraCup() { return $this->WsGeneraCup; }
	function setWsGeneraCup(WsGeneraCup $WsGeneraCup) { $this->WsGeneraCup = $WsGeneraCup; }

	
	protected $validator;
	function getValidator() { return $this->validator; }
	function setValidator($validator) { $this->validator = $validator; }
	
	protected $lastValidatorErrors=array();
	function getLastValidatorErrors() { return $this->lastValidatorErrors; }
	function setLastValidatorErrors($lastValidatorErrors) { $this->lastValidatorErrors = $lastValidatorErrors; }
	public function hasValidatorErrors() { return count($this->getLastValidatorErrors())>0 ; }
	
	
		
	public function __construct($container) {
		$this->setContainer($container);
		$validator = $this->getContainer()->get("validator");
		$this->setValidator($validator);
		$ws_genera_cup_url = $this->getParameter("cipe.ws_genera_cup.url");
		$this->setWs_genera_cup_url($ws_genera_cup_url);
		$WsGeneraCup = new WsGeneraCup();
		$this->setWsGeneraCup($WsGeneraCup);
	}

	
	// ----------------------------------------------
	
	
	// ------------------------ RICHIESTA ---------------------------
	
	
	protected function prepareSoapEnvelope($textRichiestaCupGenerazione) {
		$soapEnvelopeXml = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" ';
		$soapEnvelopeXml.= 'xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
		$soapEnvelopeXml.= '<soapenv:Body>';
		$soapEnvelopeXml.= '<richiesta_RichiestaRispostaSincrona_RichiestaGenerazioneCUP xmlns="http://serviziCUP.mef.it/types/">';
		$soapEnvelopeXml.= '<TitoloRichiesta xmlns="">Richiesta Generazione CUP</TitoloRichiesta>';
		$soapEnvelopeXml.= '<richiesta xmlns="">' . base64_encode($textRichiestaCupGenerazione) . '</richiesta>';
		$soapEnvelopeXml.= '</richiesta_RichiestaRispostaSincrona_RichiestaGenerazioneCUP>';
		$soapEnvelopeXml.= '</soapenv:Body>';
		$soapEnvelopeXml.= '</soapenv:Envelope>';
		return $soapEnvelopeXml;
	}
	
	protected function client_call($soap_request) {

		$response_up = null;
		$curlHttpStatusCode = "200";
		$resp = true;
		$curlErrorMessages = null;
		$curlResponse = '<?xml version="1.0" encoding="UTF-8"?>
	<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	 <soapenv:Body>
	  <risposta_RichiestaRispostaSincrona_EsitoGenerazioneCUP xmlns="http://serviziCUP.mef.it/types/">
	   <TitoloRisposta xmlns="">Esito Generazione CUP</TitoloRisposta>
	   <EsitoElaborazione xmlns="">ELABORAZIONE_ESEGUITA</EsitoElaborazione>
	   <risposta xmlns="">PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz48REVUVEFHTElPX0dFTkVSQVpJT05FX0NVUD48SURfUklDSElFU1RBPjU8L0lEX1JJQ0hJRVNUQT48SURfUklDSElFU1RBX0FTU0VHTkFUTz41PC9JRF9SSUNISUVTVEFfQVNTRUdOQVRPPjxERVRUQUdMSU9fRUxBQk9SQVpJT05FPjxFU0lUT19FTEFCT1JBWklPTkU+T0s8L0VTSVRPX0VMQUJPUkFaSU9ORT48REVTQ1JJWklPTkVfRVNJVE9fRUxBQk9SQVpJT05FPmNyZWF0byBDVVAgY29uIGNvZGljZTogRjQ2RDA3MDAwMTcwMDAxIFNPTk8gU1RBVEkgUklMRVZBVEkgNiBDVVAgU0lNSUxJOiAgIC0gQ1VQOiBGNDZEMDcwMDAxMTAwMDEgIHNvZ2dldHRvX3RpdG9sYXJlPSdSRUdJT05FIExBWklPJyAgdW5pdGFfb3JnYW5penphdGl2YT0nRElSRVpJT05FIFJFRy4gSVNUUlVaSU9ORSBQUk9HUkFNTUFaSU9ORSBERUxMJ09GRkVSVEEgU0NPTEFTVElDQSBFIEZPUk0uVkEgRSBESVJJVFRPIEFMTE8gU1RVRElPJyAgZGVzY3JpemlvbmVfc2ludGV0aWNhPSdwcm92YVNDVU9MQSBFTEVNRU5UQVJFIFNBTlRBIGJhcmJhcmEqVmlhIHByb3ZhVklBIERFTCBNVU5JQ0lQSU8sIDExNSpTT1NUSVRVWklPTkUgREVJIFNFUlJBTUVOVEkgRVNURVJOSSBQUkVTU08gTEEgU0NVT0xBIEVMRU1FTlRBUkUgU0FOVEEgUklUQScgIGFubm89JzIwMDcnICBjb3N0b19wcm9nZXR0bz0nMTAnICBpbXBvcnRvX2ZpbmFuemlhbWVudG9fcHViYmxpY289JzEwJyAgIC0gQ1VQOiBGNDZEMDcwMDAxMjAwMDEgIHNvZ2dldHRvX3RpdG9sYXJlPSdSRUdJT05FIExBWklPJyAgdW5pdGFfb3JnYW5penphdGl2YT0nRElSRVpJT05FIFJFRy4gSVNUUlVaSU9ORSBQUk9HUkFNTUFaSU9ORSBERUxMJ09GRkVSVEEgU0NPTEFTVElDQSBFIEZPUk0uVkEgRSBESVJJVFRPIEFMTE8gU1RVRElPJyAgZGVzY3JpemlvbmVfc2ludGV0aWNhPSdwcm92YVNDVU9MQSBFTEVNRU5UQVJFIFNBTlRBIGJhcmJhcmEqVmlhIHByb3ZhVklBIERFTCBNVU5JQ0lQSU8sIDExNSpTT1NUSVRVWklPTkUgREVJIFNFUlJBTUVOVEkgRVNURVJOSSBQUkVTU08gTEEgU0NVT0xBIEVMRU1FTlRBUkUgU0FOVEEgUklUQScgIGFubm89JzIwMDcnICBjb3N0b19wcm9nZXR0bz0nMTE4MCcgIGltcG9ydG9fZmluYW56aWFtZW50b19wdWJibGljbz0nMTE4MCcgICAtIENVUDogRjQ2RDA3MDAwMTMwMDAxICBzb2dnZXR0b190aXRvbGFyZT0nUkVHSU9ORSBMQVpJTycgIHVuaXRhX29yZ2FuaXp6YXRpdmE9J0RJUkVaSU9ORSBSRUcuIElTVFJVWklPTkUgUFJPR1JBTU1BWklPTkUgREVMTCdPRkZFUlRBIFNDT0xBU1RJQ0EgRSBGT1JNLlZBIEUgRElSSVRUTyBBTExPIFNUVURJTycgIGRlc2NyaXppb25lX3NpbnRldGljYT0ncHJvdmFTQ1VPTEEgRUxFTUVOVEFSRSBTQU5UQSBiYXJiYXJhKlZpYSBwcm92YVZJQSBERUwgTVVOSUNJUElPLCAxMTUqU09TVElUVVpJT05FIERFSSBTRVJSQU1FTlRJIEVTVEVSTkkgUFJFU1NPIExBIFNDVU9MQSBFTEVNRU5UQVJFIFNBTlRBIFJJVEEnICBhbm5vPScyMDA3JyAgY29zdG9fcHJvZ2V0dG89JzExMDg5MCcgIGltcG9ydG9fZmluYW56aWFtZW50b19wdWJibGljbz0nMTEwODkwJyAgIC0gQ1VQOiBGNDZEMDcwMDAxNDAwMDEgIHNvZ2dldHRvX3RpdG9sYXJlPSdSRUdJT05FIExBWklPJyAgdW5pdGFfb3JnYW5penphdGl2YT0nRElSRVpJT05FIFJFRy4gSVNUUlVaSU9ORSBQUk9HUkFNTUFaSU9ORSBERUxMJ09GRkVSVEEgU0NPTEFTVElDQSBFIEZPUk0uVkEgRSBESVJJVFRPIEFMTE8gU1RVRElPJyAgZGVzY3JpemlvbmVfc2ludGV0aWNhPSdwcm92YVNDVU9MQSBFTEVNRU5UQVJFIFNBTlRBIGJhcmJhcmEqVmlhIHByb3ZhVklBIERFTCBNVU5JQ0lQSU8sIDExNSpTT1NUSVRVWklPTkUgREVJIFNFUlJBTUVOVEkgRVNURVJOSSBQUkVTU08gTEEgU0NVT0xBIEVMRU1FTlRBUkUgU0FOVEEgUklUQScgIGFubm89JzIwMDcnICBjb3N0b19wcm9nZXR0bz0nMTEwOCcgIGltcG9ydG9fZmluYW56aWFtZW50b19wdWJibGljbz0nMTEwOCcgICAtIENVUDogRjQ2RDA3MDAwMTUwMDAxICBzb2dnZXR0b190aXRvbGFyZT0nUkVHSU9ORSBMQVpJTycgIHVuaXRhX29yZ2FuaXp6YXRpdmE9J0RJUkVaSU9ORSBSRUcuIElTVFJVWklPTkUgUFJPR1JBTU1BWklPTkUgREVMTCdPRkZFUlRBIFNDT0xBU1RJQ0EgRSBGT1JNLlZBIEUgRElSSVRUTyBBTExPIFNUVURJTycgIGRlc2NyaXppb25lX3NpbnRldGljYT0ncHJvdmFTQ1VPTEEgRUxFTUVOVEFSRSBTQU5UQSBiYXJiYXJhKlZpYSBwcm92YVZJQSBERUwgTVVOSUNJUElPLCAxMTUqU09TVElUVVpJT05FIERFSSBTRVJSQU1FTlRJIEVTVEVSTkkgUFJFU1NPIExBIFNDVU9MQSBFTEVNRU5UQVJFIFNBTlRBIFJJVEEnICBhbm5vPScyMDA3JyAgY29zdG9fcHJvZ2V0dG89JzEyMTA4JyAgaW1wb3J0b19maW5hbnppYW1lbnRvX3B1YmJsaWNvPScxMjEwOCcgICAtIENVUDogRjQ2RDA3MDAwMTYwMDAxICBzb2dnZXR0b190aXRvbGFyZT0nUkVHSU9ORSBMQVpJTycgIHVuaXRhX29yZ2FuaXp6YXRpdmE9J0RJUkVaSU9ORSBSRUcuIElTVFJVWklPTkUgUFJPR1JBTU1BWklPTkUgREVMTCdPRkZFUlRBIFNDT0xBU1RJQ0EgRSBGT1JNLlZBIEUgRElSSVRUTyBBTExPIFNUVURJTycgIGRlc2NyaXppb25lX3NpbnRldGljYT0ncHJvdmFTQ1VPTEEgRUxFTUVOVEFSRSBTQU5UQSBiYXJiYXJhKlZpYSBwcm92YVZJQSBERUwgTVVOSUNJUElPLCAxMTUqU09TVElUVVpJT05FIERFSSBTRVJSQU1FTlRJIEVTVEVSTkkgUFJFU1NPIExBIFNDVU9MQSBFTEVNRU5UQVJFIFNBTlRBIFJJVEEnICBhbm5vPScyMDA3JyAgY29zdG9fcHJvZ2V0dG89JzEyMTEwOCcgIGltcG9ydG9fZmluYW56aWFtZW50b19wdWJibGljbz0nMTIxMTA4JyA8L0RFU0NSSVpJT05FX0VTSVRPX0VMQUJPUkFaSU9ORT48L0RFVFRBR0xJT19FTEFCT1JBWklPTkU+PERFVFRBR0xJT19DVVA+PENPRElDRV9DVVA+RjQ2RDA3MDAwMTcwMDAxPC9DT0RJQ0VfQ1VQPjxEQVRJX0dFTkVSQUxJX1BST0dFVFRPIGNvZGljZV9wcm9nZXR0bz0iMTcxMjEyNiIgYW5ub19kZWNpc2lvbmU9IjIwMDciIGN1bXVsYXRpdm89Ik5vIiBjb2RpZmljYV9sb2NhbGU9IjIwMDYvMDkwMCIgbmF0dXJhPSJSRUFMSVpaQVpJT05FIERJIExBVk9SSSBQVUJCTElDSSAoT1BFUkUgRUQgSU1QSUFOVElTVElDQSkiIHRpcG9sb2dpYT0iUkVDVVBFUk8iIHNldHRvcmU9Ik9QRVJFIEUgSU5GUkFTVFJVVFRVUkUgU09DSUFMSSIgc290dG9zZXR0b3JlPSJTT0NJQUxJIEUgU0NPTEFTVElDSEUiIGNhdGVnb3JpYT0iU0NVT0xFIE1BVEVSTkUiIGNwdjE9IkxBVk9SSSBESSBDT1NUUlVaSU9ORS4iIGNwdjI9IkxBVk9SSSBQRVIgTEEgQ09TVFJVWklPTkUgQ09NUExFVEEgTyBQQVJaSUFMRSBFIElOR0VHTkVSSUEgQ0lWSUxFLiIgY3B2Mz0iTEFWT1JJIEdFTkVSQUxJIERJIENPU1RSVVpJT05FIERJIEVESUZJQ0kuIiBjcHY0PSJMQVZPUkkgREkgQ09TVFJVWklPTkUgREkgRURJRklDSSBQRVIgTCdJU1RSVVpJT05FIEUgTEEgUklDRVJDQS4iIGNwdjU9IkxBVk9SSSBESSBDT1NUUlVaSU9ORSBESSBFRElGSUNJIFNDT0xBU1RJQ0kuIiBjcHY2PSJTQ1VPTEEgRUxFTUVOVEFSRS4iIHN0YXRvPSJBdHRpdm8iIHByb3Z2aXNvcmlvPSJObyIgcHViYmxpY289IiIgdGlwbz0iTm9ybWFsZSIgZGF0YV9nZW5lcmF6aW9uZT0iMjgvMDUvMjAxMyIgdXRlbnRlX2dlbmVyYXRvcmVfY29tcGxldG89IndzLnJlZ2xhemlvIiB1dGVudGVfcmlmZXJpbWVudG89IndzLnJlZ2xhemlvIiBkYXRhX2dlbmVyYXppb25lX2NvbXBsZXRvPSIyOC8wNS8yMDEzIiB1dGVudGVfdWx0aW1hX21vZGlmaWNhPSJ3cy5yZWdsYXppbyIgZGF0YV91bHRpbWFfbW9kaWZpY2E9IjI4LzA1LzIwMTMiIGZhc2U9IkdlbmVyYXppb25lIi8+PE1BU1RFUi8+PExPQ0FMSVpaQVpJT05FIGRlc2NyaXppb25lPSJDb211bmUgZGkgVFJJRVNURSAoVFMpIi8+PExPQ0FMSVpaQVpJT05FIGRlc2NyaXppb25lPSJDb211bmUgZGkgQUNRVUFDQU5JTkEgKE1DKSIvPjxERVNDUklaSU9ORT48TEFWT1JJX1BVQkJMSUNJIG5vbWVfc3RyX2luZnJhc3RyPSJwcm92YVNDVU9MQSBFTEVNRU5UQVJFIFNBTlRBIGJhcmJhcmEiIHN0cl9pbmZyYXN0cl91bmljYT0iU0kiIGluZF9hcmVhX3JpZmVyPSJWaWEgcHJvdmFWSUEgREVMIE1VTklDSVBJTywgMTE1IiBkZXNjcml6aW9uZV9pbnRlcnZlbnRvPSJTT1NUSVRVWklPTkUgREVJIFNFUlJBTUVOVEkgRVNURVJOSSBQUkVTU08gTEEgU0NVT0xBIEVMRU1FTlRBUkUgU0FOVEEgUklUQSIgc3RydW1fcHJvZ3I9IkFMVFJPIiBkZXNjX3N0cnVtX3Byb2dyPSJQUk9HUkFNTUEgVFJJRU5OQUxFIExMLlBQLiAyMDA1LTIwMDciIGRlc2Nfc2ludGV0aWNhPSJwcm92YVNDVU9MQSBFTEVNRU5UQVJFIFNBTlRBIGJhcmJhcmEqVmlhIHByb3ZhVklBIERFTCBNVU5JQ0lQSU8sIDExNSpTT1NUSVRVWklPTkUgREVJIFNFUlJBTUVOVEkgRVNURVJOSSBQUkVTU08gTEEgU0NVT0xBIEVMRU1FTlRBUkUgU0FOVEEgUklUQSIvPjwvREVTQ1JJWklPTkU+PEFUVElWX0VDT05PTUlDQV9CRU5FRklDSUFSSU9fQVRFQ09fMjAwNy8+PEZJTkFOWklBTUVOVE8gc3BvbnNvcml6emF6aW9uZT0iTm9uIHByZXZpc3RlIiBmaW5hbnphX3Byb2dldHRvPSJObyIgY29zdG89IjEyMTEwNDgiIGZpbmFuemlhbWVudG89IjEyMTEwNDgiPjxERVNDUklaSU9ORV9USVBPTE9HSUFfQ09QX0ZJTkFOWj5TVEFUQUxFPC9ERVNDUklaSU9ORV9USVBPTE9HSUFfQ09QX0ZJTkFOWj48L0ZJTkFOWklBTUVOVE8+PERBVElfVElUT0xBUkVfUklDSElFREVOVEUgc29nZ2V0dG9fdGl0b2xhcmU9IlJFR0lPTkUgTEFaSU8iIHVvX3NvZ2dldHRvX3RpdG9sYXJlPSJESVJFWklPTkUgUkVHLiBJU1RSVVpJT05FIFBST0dSQU1NQVpJT05FIERFTEwnT0ZGRVJUQSBTQ09MQVNUSUNBIEUgRk9STS5WQSBFIERJUklUVE8gQUxMTyBTVFVESU8iIHVzZXJfdGl0b2xhcmU9IndzLnJlZ2xhemlvIiBzb2dnZXR0b19yaWNoaWVkZW50ZT0iUkVHSU9ORSBMQVpJTyIvPjxJTkRJQ0FUT1JFIGNvZEluZGljYXRvcmU9IjY4OSIgZGVzY0luZGljYXRvcmU9Ikdpb3JuYXRlL3VvbW8gYXR0aXZhdGUgZmFzZSBkaSBjYW50aWVyZSIgY29kVGlwb2xvZ2lhSW5kaWNhdG9yZT0iMSIgZGVzY1RpcG9sb2dpYUluZGljYXRvcmU9Ik9DQ1VQQVpJT05BTEUiLz48SU5ESUNBVE9SRSBjb2RJbmRpY2F0b3JlPSI3OTEiIGRlc2NJbmRpY2F0b3JlPSJTdXBlcmZpY2llIG9nZ2V0dG8gZGkgaW50ZXJ2ZW50byAobXEpIiBjb2RUaXBvbG9naWFJbmRpY2F0b3JlPSIyIiBkZXNjVGlwb2xvZ2lhSW5kaWNhdG9yZT0iRklTSUNPIi8+PC9ERVRUQUdMSU9fQ1VQPjwvREVUVEFHTElPX0dFTkVSQVpJT05FX0NVUD4=</risposta>
	  </risposta_RichiestaRispostaSincrona_EsitoGenerazioneCUP>
	 </soapenv:Body>
	</soapenv:Envelope>';
//		$soap_do = curl_init();
//		$ws_genera_cup_url = $this->getWs_genera_cup_url();
//		curl_setopt($soap_do, CURLOPT_URL, $ws_genera_cup_url);
//		curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 180);
//		curl_setopt($soap_do, CURLOPT_TIMEOUT, 180);
//		curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
//		curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
//		curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
//		curl_setopt($soap_do, CURLOPT_POST, true);
//		curl_setopt($soap_do, CURLOPT_POSTFIELDS, $soap_request);
//		//curl_setopt($soap_do, CURLOPT_HTTPHEADER,     $header);
//
//		$response_up = new stdClass();
//
//		$resp = curl_exec($soap_do);
//		$curlHttpStatusCode = curl_getinfo($soap_do, CURLINFO_HTTP_CODE);
//		$curlErrorMessages = curl_error($soap_do);

		$this->getWsGeneraCup()->setCurlHttpStatusCode($curlHttpStatusCode);
		$this->getWsGeneraCup()->setCurlResponse($curlResponse);
		
		$this->getWsGeneraCup()->setCurlErrorMessages($curlErrorMessages);
		if ($resp == false) {
			$response_up = false;
			$this->getWsGeneraCup()->setCurlError(true);
		} else	{
			$this->getWsGeneraCup()->setCurlError(false);
			$response_up = $this->parseResponse($curlResponse);
		}
		

		return $response_up;
	}

	
	/**
     * effettuaRichiestaCup
     * @return \StdClass mixed
     */
    public function effettuaRichiestaCup() {
		try {
			$errors = $this->getValidator()->validate($this->getWsGeneraCup()->getRichiestaCupGenerazione());
//			$validate = $this->getWsGeneraCup()->getRichiestaCupGenerazione()->validate();
			
			$erroriValidazione = array();
			if (count($errors) > 0) {
					foreach ($errors as $error) {
						$erroriValidazione[]="[".$error->getPropertyPath()."] errore: {$error->getMessage()}";
					}
			}
			$this->getWsGeneraCup()->setErroriValidazione($erroriValidazione);
			$this->getWsGeneraCup()->elabRichiestaValidaErroriValidazione();
			
			if(!$this->getWsGeneraCup()->getRichiestaValida()) throw new \Exception("Richiesta non valida.");
			
			$textRichiestaCupGenerazione = $this->getWsGeneraCup()->getRichiestaCupGenerazione()->serialize();
			$this->getWsGeneraCup()->setTextRichiestaCupGenerazione($textRichiestaCupGenerazione);
			$TextRispostaCupGenerazione  = $this->client_call($this->prepareSoapEnvelope($textRichiestaCupGenerazione));
			
			$this->getWsGeneraCup()->setTimeStampRisposta($timestampRichiesta);
			
			if(\is_null($TextRispostaCupGenerazione) || !$TextRispostaCupGenerazione) 
				throw new \Exception("errore di comunicazione soap");
			
			$this->getWsGeneraCup()->setTextRispostaCupGenerazione($TextRispostaCupGenerazione);
			return true;
		} catch (\Exception $ex) {
			throw $ex;
		}
    }
	
	
	
		// ------------------------ RISPOSTA ---------------------------

	
	public function parseResponse($xmlRespEncode) {
		try {
			
			$xmlSoapEnv = $xmlRespEncode;
			$xmlRespEncode = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $xmlRespEncode);
			$xmlRespEncode = str_replace('http://schemas.xmlsoap.org/soap/envelope/', '', $xmlRespEncode);
			$xmlRespEncode = str_replace('http://www.w3.org/2001/XMLSchema', '', $xmlRespEncode);
			$xmlRespEncode = str_replace('http://www.w3.org/2001/XMLSchema-instance', '', $xmlRespEncode);
			$xmlRespEncode = str_replace('http://serviziCUP.mef.it/types/', '', $xmlRespEncode);
			$xmlRespEncode = str_replace('<soapenv:Envelope xmlns:soapenv="" xmlns:xsd="" xmlns:xsi="-instance">', '', $xmlRespEncode);
			$xmlRespEncode = str_replace('<soapenv:Body>', '', $xmlRespEncode);
			$xmlRespEncode = str_replace('</soapenv:Body>', '', $xmlRespEncode);
			$xmlRespEncode = str_replace('</soapenv:Envelope>', '', $xmlRespEncode);


			$xmlEn = simplexml_load_string(utf8_encode($xmlRespEncode));

			$xmlRisposta = false;
			if ($xmlEn) {
				$xmlRisposta =(string) $xmlEn->risposta;
				$xmlRisposta = base64_decode($xmlRisposta);
			}

			return $xmlRisposta;
		
			
		} catch (\Exception $ex) {
			throw $ex;
		}
	}
		

}
