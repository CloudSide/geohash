<?php
/**
 * Geohash generation class for php 
 *
 * This file copyright (C) 2013 Bruce Chen (http://weibo.com/smcz)
 *
 * Author: Bruce Chen (weibo: @一个开发者)
 *
 */


/**
 *
 * Encode and decode geohashes
 *
 * Find neighbors
 *
 */

class Geohash {

	private $bitss = array(16, 8, 4, 2, 1);
	private $neighbors = array();
	private $borders = array();
	
	private $coding = "0123456789bcdefghjkmnpqrstuvwxyz";
	private $codingMap = array();
	
	public function Geohash() {
		
		$this->neighbors['right']['even'] = 'bc01fg45238967deuvhjyznpkmstqrwx';
		$this->neighbors['left']['even'] = '238967debc01fg45kmstqrwxuvhjyznp';
		$this->neighbors['top']['even'] = 'p0r21436x8zb9dcf5h7kjnmqesgutwvy';
		$this->neighbors['bottom']['even'] = '14365h7k9dcfesgujnmqp0r2twvyx8zb';
		
		$this->borders['right']['even'] = 'bcfguvyz';
		$this->borders['left']['even'] = '0145hjnp';
		$this->borders['top']['even'] = 'prxz';
		$this->borders['bottom']['even'] = '028b';
		
		$this->neighbors['bottom']['odd'] = $this->neighbors['left']['even'];
		$this->neighbors['top']['odd'] = $this->neighbors['right']['even'];
		$this->neighbors['left']['odd'] = $this->neighbors['bottom']['even'];
		$this->neighbors['right']['odd'] = $this->neighbors['top']['even'];
		
		$this->borders['bottom']['odd'] = $this->borders['left']['even'];
		$this->borders['top']['odd'] = $this->borders['right']['even'];
		$this->borders['left']['odd'] = $this->borders['bottom']['even'];
		$this->borders['right']['odd'] = $this->borders['top']['even'];
		
		//build map from encoding char to 0 padded bitfield
		for($i=0; $i<32; $i++) {
		
			$this->codingMap[substr($this->coding, $i, 1)] = str_pad(decbin($i), 5, "0", STR_PAD_LEFT);
		}
		
	}
	
	/**
	* Decode a geohash and return an array with decimal lat,long in it
	* Author: Bruce Chen (weibo: @一个开发者)
	*/
	public function decode($hash) {
	
		//decode hash into binary string
		$binary = "";
		$hl = strlen($hash);
		for ($i=0; $i<$hl; $i++) {
		
			$binary .= $this->codingMap[substr($hash, $i, 1)];
		}
		
		//split the binary into lat and log binary strings
		$bl = strlen($binary);
		$blat = "";
		$blong = "";
		for ($i=0; $i<$bl; $i++) {
		
			if ($i%2)
				$blat=$blat.substr($binary, $i, 1);
			else
				$blong=$blong.substr($binary, $i, 1);
			
		}
		
		//now concert to decimal
		$lat = $this->binDecode($blat, -90, 90);
		$long = $this->binDecode($blong, -180, 180);
		
		//figure out how precise the bit count makes this calculation
		$latErr = $this->calcError(strlen($blat), -90, 90);
		$longErr = $this->calcError(strlen($blong), -180, 180);
				
		//how many decimal places should we use? There's a little art to
		//this to ensure I get the same roundings as geohash.org
		$latPlaces = max(1, -round(log10($latErr))) - 1;
		$longPlaces = max(1, -round(log10($longErr))) - 1;
		
		//round it
		$lat = round($lat, $latPlaces);
		$long = round($long, $longPlaces);

		return array($lat, $long);
	}

	
	private function calculateAdjacent($srcHash, $dir) {
	
		$srcHash = strtolower($srcHash);
		$lastChr = $srcHash[strlen($srcHash) - 1];
		$type = (strlen($srcHash) % 2) ? 'odd' : 'even';
		$base = substr($srcHash, 0, strlen($srcHash) - 1);
		
		if (strpos($this->borders[$dir][$type], $lastChr) !== false) {
			
			$base = $this->calculateAdjacent($base, $dir);	
		}
			
		return $base . $this->coding[strpos($this->neighbors[$dir][$type], $lastChr)];
	}
	
	
	public function neighbors($srcHash) {
	
		$geohashPrefix = substr($srcHash, 0, strlen($srcHash) - 1);
	 
	 	$neighbors['top'] = $this->calculateAdjacent($srcHash, 'top');
	 	$neighbors['bottom'] = $this->calculateAdjacent($srcHash, 'bottom');
	 	$neighbors['right'] = $this->calculateAdjacent($srcHash, 'right');
	 	$neighbors['left'] = $this->calculateAdjacent($srcHash, 'left');
	 	
	 	$neighbors['topleft'] = $this->calculateAdjacent($neighbors['left'], 'top');
	 	$neighbors['topright'] = $this->calculateAdjacent($neighbors['right'], 'top');
	 	$neighbors['bottomright'] = $this->calculateAdjacent($neighbors['right'], 'bottom');
	 	$neighbors['bottomleft'] = $this->calculateAdjacent($neighbors['left'], 'bottom');
	 
		return $neighbors;
	}


	
	/**
	* Encode a hash from given lat and long
	* Author: Bruce Chen (weibo: @一个开发者)
	*/
	public function encode($lat, $long) {
	
		//how many bits does latitude need?	
		$plat = $this->precision($lat);
		$latbits = 1;
		$err = 45;
		while($err > $plat) {
		
			$latbits++;
			$err /= 2;
		}
		
		//how many bits does longitude need?
		$plong = $this->precision($long);
		$longbits = 1;
		$err = 90;
		while($err > $plong) {
		
			$longbits++;
			$err /= 2;
		}
		
		//bit counts need to be equal
		$bits = max($latbits, $longbits);
		
		//as the hash create bits in groups of 5, lets not
		//waste any bits - lets bulk it up to a multiple of 5
		//and favour the longitude for any odd bits
		$longbits = $bits;
		$latbits = $bits;
		$addlong = 1;
		while (($longbits + $latbits) % 5 != 0) {
		
			$longbits += $addlong;
			$latbits += !$addlong;
			$addlong = !$addlong;
		}
		
		
		//encode each as binary string
		$blat = $this->binEncode($lat, -90, 90, $latbits);
		$blong = $this->binEncode($long, -180, 180, $longbits);
		
		//merge lat and long together
		$binary = "";
		$uselong = 1;
		while (strlen($blat) + strlen($blong)) {
		
			if ($uselong) {
			
				$binary = $binary.substr($blong, 0, 1);
				$blong = substr($blong, 1);
			
			} else {
			
				$binary = $binary.substr($blat, 0, 1);
				$blat = substr($blat, 1);
			}
			
			$uselong = !$uselong;
		}
		
		//convert binary string to hash
		$hash = "";
		for ($i=0; $i<strlen($binary); $i+=5) {
		
			$n = bindec(substr($binary, $i, 5));
			$hash = $hash.$this->coding[$n];
		}
		
		return $hash;
	}
	
	/**
	* What's the maximum error for $bits bits covering a range $min to $max
	*/
	private function calcError($bits, $min, $max) {
	
		$err = ($max - $min) / 2;
		while ($bits--)
			$err /= 2;
		return $err;
	}
	
	/*
	* returns precision of number
	* precision of 42 is 0.5
	* precision of 42.4 is 0.05
	* precision of 42.41 is 0.005 etc
	*
	* Author: Bruce Chen (weibo: @一个开发者)
	*/
	private function precision($number) {
	
		$precision = 0;
		$pt = strpos($number,'.');
		if ($pt !== false) {
		
			$precision = -(strlen($number) - $pt - 1);
		}
		
		return pow(10, $precision) / 2;
	}
	
	
	/**
	* create binary encoding of number as detailed in http://en.wikipedia.org/wiki/Geohash#Example
	* removing the tail recursion is left an exercise for the reader
	* 
	* Author: Bruce Chen (weibo: @一个开发者)
	*/
	private function binEncode($number, $min, $max, $bitcount) {
	
		if ($bitcount == 0)
			return "";
		
		#echo "$bitcount: $min $max<br>";
			
		//this is our mid point - we will produce a bit to say
		//whether $number is above or below this mid point
		$mid = ($min + $max) / 2;
		if ($number > $mid)
			return "1" . $this->binEncode($number, $mid, $max, $bitcount - 1);
		else
			return "0" . $this->binEncode($number, $min, $mid, $bitcount - 1);
	}
	

	/**
	* decodes binary encoding of number as detailed in http://en.wikipedia.org/wiki/Geohash#Example
	* removing the tail recursion is left an exercise for the reader
	* 
	* Author: Bruce Chen (weibo: @一个开发者)
	*/
	private function binDecode($binary, $min, $max) {
	
		$mid = ($min + $max) / 2;
		
		if (strlen($binary) == 0)
			return $mid;
			
		$bit = substr($binary, 0, 1);
		$binary = substr($binary, 1);
		
		if ($bit == 1)
			return $this->binDecode($binary, $mid, $max);
		else
			return $this->binDecode($binary, $min, $mid);
	}
}





