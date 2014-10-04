<?php

/*
 *  Bencoder
 *  Tijn Kersjes
 *  19-3-2011
 *
 *  Encodes data to BEncode strings
 */

function bencode($data){
	if(is_string($data)){
		$len = (string) strlen($data);
		$cdata = $len.':'.$data;
		return $cdata;
	}
	else if(is_int($data)){
		$cdata = 'i'.$data.'e';
		return $cdata;
	}
}

class bencodeDict{
	private $str;
	public function __construct($arr){
		$this->str = 'd';
		ksort($arr);
		foreach($arr AS $k => $v){
			if(is_object($v)){
				$this->str .= bencode($k).($v);
			}
			else{
				$this->str .= bencode($k).bencode($v);
			}
		}
		$this->str .= 'e';
	}
	public function __toString(){
		return $this->str;
	}
}

class bencodeList{
	private $str;
	public function __construct($arr){
		$this->str = 'l';
		sort($arr);
		foreach($arr AS $v){
			if(is_object($v)){
				$this->str .= $v;
			}
			else{
				$this->str .= bencode($v);
			}
		}
		$this->str .= 'e';
	}
	public function __toString(){
		return $this->str;
	}
}
?>
