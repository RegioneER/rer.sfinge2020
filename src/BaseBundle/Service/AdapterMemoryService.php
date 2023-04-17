<?php


namespace BaseBundle\Service;


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *  AdapterMemoryService
 * classe servizio per gestione ram adattativa
 * utile in caso di operazioni massive
 *
 * @author gaetanoborgosano
 */
class AdapterMemoryService {

	
	protected $maxMemory = 536870912; // 512 M
	function getMaxMemory() { return $this->maxMemory; }
	function setMaxMemory($maxMemory) { $this->maxMemory = $maxMemory; }

	protected $increaseStepMemory = 52428800; // 50 M
	function getIncreaseStepMemory() { return $this->increaseStepMemory; }
	function setIncreaseStepMemory($increaseStepMemory) { $this->increaseStepMemory = $increaseStepMemory; }
	
	protected $originMemoryLimit;
	function getOriginMemoryLimit() { return $this->originMemoryLimit; }
	function setOriginMemoryLimit($originMemoryLimit) { $this->originMemoryLimit = $originMemoryLimit; }

		
	protected $currentMemoryLimit;
	function getCurrentMemoryLimit() { return $this->currentMemoryLimit; }
	function setCurrentMemoryLimit($currentMemoryLimit) { $this->currentMemoryLimit = $currentMemoryLimit; }
	
	protected $currentAllocatedMemory;
	function getCurrentAllocatedMemory() { return $this->currentAllocatedMemory; }
	function setCurrentAllocatedMemory($currentAllocatedMemory) { $this->currentAllocatedMemory = $currentAllocatedMemory; }
		
	protected $allertMemoryRatio = 0.70;
	function getAllertMemoryRatio() { return $this->allertMemoryRatio; }
	function setAllertMemoryRatio($allertMemoryRatio) { $this->allertMemoryRatio = $allertMemoryRatio; }


	protected $useGarbageCollectionMemory = true;
	function getUseGarbageCollectionMemory() { return $this->useGarbageCollectionMemory; }
	function setUseGarbageCollectionMemory($useGarbageCollectionMemory) { $this->useGarbageCollectionMemory = $useGarbageCollectionMemory; }

	protected $sliceGcCycles = 10;
	function getSliceGcCycles() { return $this->sliceGcCycles; }
	function setSliceGcCycles($sliceGcCycles) { $this->sliceGcCycles = $sliceGcCycles; }

	protected $counterGcCycles = 0;
	function getCounterGcCycles() { return $this->counterGcCycles; }
	function setCounterGcCycles($counterGcCycles) { $this->counterGcCycles = $counterGcCycles; }
	function increaseCounterGcCycles() { $counterGcCycles = $this->getCounterGcCycles(); $this->setCounterGcCycles( (++$counterGcCycles) );}

			
	protected function return_bytes($val) {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
		$val = (int)$val;
        switch($last) {
            // The 'G' modifier is available since PHP 5.1.0
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }

        return $val;
    }  
	
	protected function freeGcCycles() {
		if($this->getUseGarbageCollectionMemory()) {
			$this->increaseCounterGcCycles();
			if(	$this->getCounterGcCycles() > $this->getSliceGcCycles() ) {
				gc_collect_cycles(); gc_disable(); gc_enable(); memory_get_peak_usage(true);
				$this->setCounterGcCycles(0);
//				echo "<p><h1>LIBERA</h1></p>";

			}
			
		}
	}
	
	protected function iniset_memory($byteMemory) {
		$amountOfMemory = ceil(($byteMemory / 1024) / 1024);
		$amountOfMemoryInit = $amountOfMemory."M";
//		echo "<p><h1> ----------------------  INIT_SET:[$amountOfMemoryInit] --------------------</h1></p><br><br>";
		if($this->getUseGarbageCollectionMemory()) {
		    gc_collect_cycles(); gc_disable(); gc_enable(); memory_get_peak_usage(true);
			usleep(200);
		}
		ini_set('memory_limit',"$amountOfMemoryInit");
	}
	
	public function adaptMemory() {
		$this->freeGcCycles();
		$currentAllocatedMemory = $this->getCurrentAllocatedMemory();
		$memoryUsage = \memory_get_usage(true);
		
		$memory_limit = $this->getAllertMemoryRatio() * $currentAllocatedMemory;
//		echo "<p>usage:[$memoryUsage] ini_set:[$currentAllocatedMemory]</p>\n";
		if($memory_limit > 0 && ($memoryUsage > $memory_limit )) {
//			echo "<p>----------SUPERATO</p><br><br>\n\n";
			$increaseStepMemory = $this->getIncreaseStepMemory();
			$newMemory = $currentAllocatedMemory + $increaseStepMemory;
//			echo "<p>----- NEW: [$newMemory]</p><br><br>\n\n";
			if($newMemory <= $this->getMaxMemory()) {
				$this->setCurrentAllocatedMemory($newMemory);
				$this->iniset_memory($newMemory);
				return true;
			}
			return false;
		}
		return true;
	}
	
	
	public function start($useGarbageCollectionMemory = true) {
		$this->setUseGarbageCollectionMemory($useGarbageCollectionMemory);
		$originMemoryLimit = ini_get('memory_limit');
		$this->setOriginMemoryLimit($originMemoryLimit);

		$currentMemoryLimit = $this->return_bytes($originMemoryLimit);
		$this->setCurrentMemoryLimit($currentMemoryLimit);
		$this->setCurrentAllocatedMemory($currentMemoryLimit);
		if($useGarbageCollectionMemory) {
			\gc_enable();
		}
	}
	
	
	
	public function end() {
		$originMemoryLimit = $this->getOriginMemoryLimit();
		if($this->getUseGarbageCollectionMemory()) \gc_disable();
		ini_set('memory_limit',$originMemoryLimit);
	}
	

}
