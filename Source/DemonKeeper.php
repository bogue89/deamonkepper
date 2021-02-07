<?php

namespace DemonKeeper;

use Exception;

class DemonKeeper {
		
	protected $hell = null;
	protected $demon = null;
	protected $keeper = null;
	protected $logger = null;
	protected $pid = null;
	protected $options = array(
		'hell' => 'php',
		'throw' => true,
		'keeper' => null,
		'logger' => null,
		'dirname' => null,
		'basename' => null,
		'extension' => null,
		'filename' => null,
		'background' => true,
		'vars' => [],
		'DS' => DIRECTORY_SEPARATOR
	);
	public $_errors = array();
	
	public function __construct($script, $options=array()) {
		if(file_exists($script)) {
			$this->options = array_merge($this->options, pathinfo(realpath($script)));
			$this->options = array_merge($this->options, $options);
			$this->options = array_merge([
				'logger' => $this->options['dirname'],
				'keeper' => $this->options['dirname'],
			], $this->options);		
			$this->demon  = $this->options['basename'];
			$this->vars  = $this->options['vars'];
			$this->hell    = $this->options['hell'];
			$this->keeper  =  $this->options['keeper'].$this->options['DS'].$this->options['filename'].implode('_', $this->vars).'.'.'pid';
			$this->logger  =  $this->options['logger'].$this->options['DS'].$this->options['filename'].'.'.'log';
		} else {
			$this->_throw(1);
		}
	}
	public function log($output) {
		if(is_array($output)) {
			$output = implode("\n", $output);
		}
		try {
			if(fwrite(fopen($this->logger, "a"), date('Y-m-d H:i:s')." ".$output."\n") === false) {
				$this->_throw(2);	
			}
		} catch(Exception $e) {
			$this->_throw(2);
		}
	}
	public function lock() {
		try {
			if(is_numeric($this->pid) && fwrite(fopen($this->keeper, "w"), $this->pid) !== false) {
				return true;
			} else {
				$this->_throw(3);
			}
		} catch(Exception $e) {
			$this->_throw(3);
		}
		return false;
	}
	public function unlock() {
		$this->pid = null;
		try {
			if(fwrite(fopen($this->keeper, "w"), $this->pid) !== false) {
				return true;
			} else {
				$this->_throw(3);
			}
		} catch(Exception $e) {
			$this->_throw(3);
		}
		return false;
	}
	public function getLock() {
		try {
			if(!file_exists($this->keeper)) {
				fopen($this->keeper, "w+");
			}
			return file_get_contents($this->keeper);
		} catch(Exception $e) {
			$this->_throw(3);
		}
		return false;
	}
	public function isAlive() {
		$this->pid = (int) $this->getLock();
		$status = $this->status();
		return isset($status['stat']);
	}
	public function status(){
		exec("ps aux {$this->pid}", $ps);
		$out = $pa1 = $pa2 = [];
		if(count($ps) == 2) {
			preg_match_all("/\S+/", $ps[0], $ma);
			$pa1 = $ma[0];
			preg_match_all("/\S+/", $ps[1], $ma);
			$pa2 = $ma[0];
		}
		if(count($pa1) > 9 && count($pa2) > 9) {
			for($n=0; $n<10; $n++) {
				$out[strtolower($pa1[$n])] = $pa2[$n];
			}	
		}
		return $out; 
	} 
	public function start(&$output=null) {
		if($this->isAlive()) {
			$this->_throw(6);
			return false;	
		}
		$cchars = " ,+*?[^]($)<>|\"'";
		$spell  = "{$this->hell} ";
		$spell .= addcslashes("{$this->options['dirname']}{$this->options['DS']}{$this->demon}", $cchars);
		if(!empty($this->vars)) {
			$spell .= " ".implode(' ', $this->vars);
		}
		$this->log("Demon starting");
		if($this->options['background']) {
			$spell .= " >>".addcslashes($this->logger, $cchars)." & echo $!";
			$this->pid = (int) exec($spell, $output);
		} else {
			$spell .= " 2>&1 & echo $!";
			$this->log("Demon starting");
			exec($spell, $output);
			$this->pid = (int) array_shift($output);
			if(count($output) > 0) {
				$this->log($output);
			}
		}
		$this->log("Demon {$this->pid} started");
		return $this->lock();
	}
	public function stop() {
		if($this->isAlive()) {
			exec("kill {$this->pid} 2>&1", $output, $return_value);
			if($return_value > 0) {
				$this->_throw(5);
				$this->log("Demon throw error 5");
			}
			$this->log("Demon {$this->pid} stopping");
			return $this->unlock();
		} else {
			$this->log("Demon throw error 4");
			$this->_throw(4);
		}
		return false;
	}
	private function _throw($code) {
		$error = new DemonKeeperException($code);
		if($this->options['throw']) {
			throw $error;
		} else {
			$this->_errors += [$error];
		}
	}
}
class DemonKeeperException extends Exception {
	protected $errors = [
		1 => 'No script found',
		2 => 'Couldn\'t log script',
		3 => 'Couldn\'t lock the demon',
		4 => 'There is no demon alive',
		5 => 'There was an error killing the demon',
		6 => 'This demon is alive'
	];
	public function __construct($code) {
		return parent::__construct($this->errors[$code], $code);
	}
}