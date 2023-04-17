<?php

namespace BaseBundle\Service;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UsernameService
{
	const COMMAND_LINE_USERNAME = '<<command>>';
	
	/**
	 * @var TokenStorageInterface
	 */
	protected $tokenStorage;
	
	public function __construct(TokenStorageInterface $storage){
		$this->tokenStorage = $storage;
	}

	public function getUserName(): ?string{
		$token =  $this->tokenStorage->getToken();
		if(\is_null($token)){
			return self::COMMAND_LINE_USERNAME;
		}
		return $token->getUsername();
	}

	public function __toString(){
		return $this->getUserName();
	}
}