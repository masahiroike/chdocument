<?php

/**
 *         ,,               ,,
 *        dM4              ]M[
 *   ;gmm dM4;mm,   ggmu_  ]M[ ,my  _gmm,
 * _MN""" dMN""MML  " _WWL ]M[gNF  ]MF "MN
 * ]M[    dM4  ]M[ yMPPNM[ ]MWMN.  MN   ]NL
 * .MNg_, dM4  ]M[ MN__dM[ ]M[ MN, ]Mk_uMP
 *   ""P"  "'  ""'  "P"""' ""'  ""  ""Y""
 *
 * @package chdocument
 * @version 1.0 beta
 * @author masahiro ike <masachako0520@gmail.com>
 * @copyright Copyright (c) 2014 masahiro ike
 * @license http://opensource.org/licenses/mit-license.php MIT License
 * @link http://masahiroike.com/
 *
 **/


//class documentException extends Exception {}
//class chdocument extends PHPUnit_Framework_TestCase {
class chdocument {

	protected $self = null;
	protected $pos = 0;
	protected $length = -1;
	protected $selecter = '';
	protected $document = '';
	protected $itemHistory = 99999999999;
	protected $posHistory = 0;
	protected $endExsist = false;
	protected $exclusion = array();
	protected $item = -1;
	protected $changeId = 0;
	public $history = array();

	protected $noneEndTags = array(
		'br', 'hr', 'img', 'input', 'meta', 'area', 'base', 'col', 'link', 'param', '!doctype', '!DOCTYPE', '?xml', '?XML', 'keygen', 'source'
	);
	protected $omissionTags = array(
		'dt', 'dd', 'li', 'rt', 'rp', 'optgroup', 'option', 'tr', 'td', 'th', 'tfoot', 'tbody'
	);
	protected $omissionParentTags = array(
		'dt' => array('dl'),
		'dd' => array('dl'),
		'li' => array('ul', 'ol'),
		'rt' => array('ruby'),
		'rp' => array('ruby'),
		'optgroup' => array('select'),
		'option' => array('select'),
		'tr' => array('table', 'thead', 'tbody', 'tfoot'),
		'td' => array('tr'),
		'th' => array('tr'),
		'tfoot' => array('table'),
		'tbody' => array('table'),
	);
	protected $omissionPairTags = array(
		'dt' => 'dd',
		'dd' => 'dt',
		'rt' => 'rp',
		'rp' => 'rt',
		'option' => 'optgroup',
		'td' => 'th',
		'th' => 'td',
		'tfoot' => 'tbody',
		'tbody' => 'tfoot',
	);

	public function __construct($data = '', $textCode = 'auto', $pos = 0, $length = -1, $endExsist = false, $selecter = '', $item = -1, $flag = false, $history = null) {

		$this -> pos = $pos;
		$this -> length = $length;
		
		$this -> endExsist = $endExsist;
		$this -> selecter = $selecter;
		$this -> item = $item;


		if(is_object($data)) {
			$this -> self = $data;
			$this -> changeId = $this -> self -> changeId;
			$this -> history = $history;
			if($flag === true) $this -> history[] = array($this -> endExsist, $this -> selecter, $this -> item);
		}
		else if(strpos($data, 'http', 0) === 0) {
			$this -> document = file_get_contents($data);
			switch($textCode) {
				case 'auto':
					preg_match('/charset="?(.*?)"/', $this -> document, $char);
					if(!empty($char[1])) {
						$this -> document = mb_convert_encoding($this -> document, mb_internal_encoding(), $char[1]);
					}
					break;
				case 'none': break;
				default:
					$this -> document = mb_convert_encoding($this -> document, mb_internal_encoding(), $textCode);
			}

			$this -> self = $this;
			$this -> self -> exclusion = $this -> eliminate();
			$this -> length = strlen($this -> self -> document);
		}
		else if(is_string($data) && $data !== '') {
			$this -> document = $data;
			$this -> self = $this;
			$this -> self -> exclusion = $this -> eliminate();
			$this -> length = strlen($this -> self -> document);
		}
	}

	public function __toString() {
		if($this -> changeId !== $this -> self -> changeId) $this -> updata();
		return $this -> document();
	}

	public function __set($name, $value) {
		$this -> self -> changeId = $this -> self -> changeId + 1;
		switch($name) {
			case 'innerHTML':

				$result = $this -> inner($this -> self -> document, $this -> pos, $this -> length);
				$this -> eliminateDelete($result[0], $result[1]);
				$this -> self -> exclusion[$result[0]] = $value;
				$this -> self -> document = $this -> setDummy($this -> self -> document, $result[0], $result[1]);

				break;
			case 'outerHTML':

				$this -> eliminateDelete($this -> pos, $this -> length);
				$this -> self -> exclusion[$this -> pos] = $value;
				$this -> self -> document = $this -> setDummy($this -> self -> document, $this -> pos, $this -> length);

				break;
			default:
				$this -> self -> document = $this -> setAttribute($this -> self -> document, $this -> pos, $name, $value);
		}
		$this -> updata();
	}

	public function __get($name) {
		if($this -> changeId !== $this -> self -> changeId) $this -> updata();
		switch($name) {
			case 'text':
				return $this -> text($this -> self -> document, $this -> pos, $this -> length);
				break;
			case 'tagName':
				return $name = $this -> currentTagName($this -> self -> document, $this -> pos);
				break;
			case 'className':
				return $this -> getAttribute($this -> self -> document, $this -> pos, 'class');
				break;
			case 'length':
				return $this -> length($this -> self -> document, $this -> pos, $this -> length, $this -> selecter);
				break;
			case 'parentNode':
				$result = $this -> parentNode($this -> self -> document, $this -> pos);
				return new chdocument($this -> self, mb_internal_encoding(), $result[0], $result[1], $result[2]);
				break;
			case 'children':
				$result = $this -> childNodes($this -> self -> document, $this -> pos);
				$constructs = array();
				for($i = 0, $count = count($result); $i < $count; $i++) {
					$constructs[$i] = new chdocument($this -> self, mb_internal_encoding(), $result[$i][0], $result[$i][1], $result[$i][2]);
				}
				return $constructs;
				break;
			case 'innerHTML':
				$result = $this -> inner($this -> self -> document, $this -> pos, $this -> length);

				$document = substr($this -> self -> document, $result[0], $result[1]);
				$temp = $this -> self -> exclusion;
				krsort($temp);
				foreach($temp as $key => $value) {
					if($key > $result[0] && $key < $result[0] + $result[1]) {
						$document = $this -> addString($document, $key - $result[0], $value);
					}
				}
				return $document;
				break;
			case 'outerHTML':
				return $this -> document();
				break;
			default:
				return $this -> getAttribute($this -> self -> document, $this -> pos, $name);
		}
		return $this;
	}

	public function __call($name, $args) {
		if($this -> changeId !== $this -> self -> changeId) $this -> updata();
		switch($name) {
			case 'getElementsByTagName':
				return new chdocument($this -> self, mb_internal_encoding(), $this -> pos, $this -> length, false, '<'. $args[0], $this -> item, false, $this -> history);
				break;
			case 'getElementsByClassName':
				return new chdocument($this -> self, mb_internal_encoding(), $this -> pos, $this -> length, false, '.'. $args[0], $this -> item, false, $this -> history);
				break;
			case 'getElementsByName':
				return new chdocument($this -> self, mb_internal_encoding(), $this -> pos, $this -> length, false, '!'. $args[0], $this -> item, false, $this -> history);
				break;
			case 'getElementById':
				$result = $this -> getElementByAttribute($this -> self -> document, $this -> pos, $this -> length, $args[0], 0, 'id');
				return new chdocument($this -> self, mb_internal_encoding(), $result[0], $result[1], $result[2], '#'. $args[0], 0, true, $this -> history);
				break;
			case 'item':
				switch(substr($this -> selecter, 0, 1)) {
					case '.':
						$result = $this -> getElementByAttribute($this -> self -> document, $this -> pos, $this -> length, substr($this -> selecter, 1), $args[0], 'class');
						return new chdocument($this -> self, mb_internal_encoding(), $result[0], $result[1], $result[2], $this -> selecter, $args[0], true, $this -> history);
						break;
					case '<':
						$result = $this -> getElementsByTag($this -> self -> document, $this -> pos, $this -> length, $this -> selecter, $args[0]);
						return new chdocument($this -> self, mb_internal_encoding(), $result[0], $result[1], $result[2], $this -> selecter, $args[0], true, $this -> history);
						break;
					case '!':
						$result = $this -> getElementByAttribute($this -> self -> document, $this -> pos, $this -> length, substr($this -> selecter, 1), $args[0], 'name');
						return new chdocument($this -> self, mb_internal_encoding(), $result[0], $result[1], $result[2], $this -> selecter, $args[0], true, $this -> history);
						break;
					case '#':
						$result = $this -> getElementByAttribute($this -> self -> document, $this -> pos, $this -> length, substr($this -> selecter, 1), 0, 'id');
						return new chdocument($this -> self, mb_internal_encoding(), $result[0], $result[1], $result[2], $this -> selecter, $args[0], true, $this -> history);
						break;
				}
				break;
		}
		return $this;
	}

	protected function clearNbsp($document) {
		$pos = 0;
		while(($pos = strpos($document, '  ', $pos)) !== false) {
			$document = $this -> deleteString($document, $pos, 1);
		}
		return $document;
	}

	protected function updata() {

		$temp = $this -> self -> exclusion;
		krsort($temp);
		foreach($temp as $key => $value) {
			$this -> self -> document = $this -> addString($this -> self -> document, $key, $value);
		}

		$this -> self -> document = $this -> clearNbsp($this -> self -> document);
		$this -> self -> exclusion = $this -> self -> eliminate();
		$this -> self -> length = strlen($this -> self -> document);

		$pos = 0;
		$length = $this -> self -> length;
		$flag = false;

		for($i = 0, $count = count($this -> history); $i < $count; $i++) {
			switch(substr($this -> history[$i][1], 0, 1)) {
				case '.':
					$result = $this -> getElementByAttribute($this -> self -> document, $pos, $length, substr($this -> history[$i][1], 1), $this -> history[$i][2], 'class');
					break;
				case '<':
					$result = $this -> getElementsByTag($this -> self -> document, $pos, $length, $this -> history[$i][1], $this -> history[$i][2]);
					break;
				case '!':
					$result = $this -> getElementByAttribute($this -> self -> document, $pos, $length, substr($this -> history[$i][1], 1), $this -> history[$i][2], 'name');
					break;
				case '#':
					$result = $this -> getElementByAttribute($this -> self -> document, $pos, $length, substr($this -> history[$i][1], 1), $this -> history[$i][2], 'id');
					break;
			}
			$pos = $result[0];
			$length = $result[1];
			$flag = true;
		}

		if($flag === true) {
			$this -> pos = $result[0];
			$this -> length = $result[1];
			$this -> endExsist = $result[2];
		}

		$this -> itemHistory = 99999999999;
		$this -> changeId = $this -> self -> changeId;

	}

	protected function document() {
		$document = substr($this -> self -> document, $this -> pos, $this -> length);
		$temp = $this -> self -> exclusion;
		krsort($temp);
		foreach($temp as $key => $value) {
			if($key > $this -> pos && $key < $this -> pos + $this -> length) {
				$document = $this -> addString($document, $key - $this -> pos, $value);
			}
		}
		return $document;
	}

	protected function eliminateDelete($pos, $length) {
		foreach($this -> self -> exclusion as $key => $value) {
			if($pos < $key && $pos + $length > $key ) {
				unset($this -> self -> exclusion[$key]);
			}
		}
	}

	protected function eliminate() {
		$pos = 0;
		$temp = array();
		while(($pos = strpos($this -> self -> document, '<script', $pos))) {
			$posEnd = strpos($this -> self -> document, '</script', $pos) + 9;
			$temp[$pos] = $posEnd - $pos;
			$pos = $posEnd;
		}
		$pos = 0;
		while(($pos = strpos($this -> self -> document, '<!--', $pos))) {
			$posEnd = strpos($this -> self -> document, '-->', $pos) + 3;
			$temp[$pos] = $posEnd - $pos;
			$pos = $posEnd;
		}

		ksort($temp);

		$result = array();
		$count = 0;
		foreach($temp as $key => $value) {
			$result[$key - $count] = substr($this -> self -> document, $key - $count, $value);
			$this -> self -> document = $this -> deleteString($this -> self -> document, $key - $count, $value);
			$count = $count + $value;
		}

		return $result;
	}

	protected function text($document, $pos, $length) {
		$document = substr($document, $pos, $length);
		$pos = 0;
		while(($pos = strpos($document, '<', $pos)) !== false) {
			$length = strpos($document, '>', $pos) - $pos + 1;
			if($length === false) return $document;
			$document = $this -> deleteString($document, $pos, $length);
		}
		return $document;
	}

	protected function split($stiring, $cepa) {
		$pos = 0;
		$posTemp = 0;
		$result = array();
		while(($pos = strpos($stiring, $cepa, $pos)) !== false) {
			$str = substr($stiring, $posTemp, $pos - $posTemp);
			if($str !== '' && $str !== false) $result[] = $str;
			$pos = $pos + strlen($cepa);
			$posTemp = $pos;
		}
		$str = substr($stiring, $posTemp);
		if($str !== '' && $str !== false) $result[] = $str;
		return $result;
	}

	protected function inner($document, $pos, $length) {
		$posDefault = $pos;
		$name = $this -> currentTagName($document, $pos);
		$pos = strpos($document, '>', $pos) + 1;

		$endTagLen = 0;
		if($this -> endExsist === true) $endTagLen = strlen($name) + 3;

		return array($pos, $length - ($pos - $posDefault) - $endTagLen);
	}

	protected function length($document, $pos, $length, $selecter) {
		$posEnd = $pos + $length;
		$i = 0;
		$name = substr($selecter, 1);
		switch(substr($selecter, 0, 1)) {
			case '.':
				return $this -> lengthAttribute($document, $pos, $name, 'class');
				break;
			case '<':
				$name = '<'. $name;
				while(($pos = strpos($document, $selecter, $pos)) !== false) {
					if($posEnd < $pos) return $i;
						if(substr($document, $pos, strlen($name) + 1) === $name. ' ' || substr($document, $pos, strlen($name) + 1) === $name. '>') {
							$i = $i + 1;
						}
						$pos = $pos + 1;
				}
				return $i;
				break;
			case '!':
				return $this -> lengthAttribute($document, $pos, $name, 'name');
				break;
			case '#':
				return $this -> lengthAttribute($document, $pos, $name, 'id');
				break;
		}
	}

	protected function currentTagName($document, $pos = 0) {
		$pos = $pos + 1;
		$temp1 = strpos($document, ' ', $pos);
		$temp2 = strpos($document, '>', $pos);

		if($temp1 !== false && $temp2 !== false) {
			$posEnd = $temp1 < $temp2? $temp1: $temp2;
		}
		else if($temp1 !== false) {
			$posEnd = $temp1;
		}
		else if($temp2 !== false) {
			$posEnd = $temp2;
		}

		return substr($document, $pos, $posEnd - $pos);
	}

	protected function parentTagName($document, $pos = 0, $option = 'name') {
		$omissionTags = array(
				'li' => array('li'), 
				'dd' => array('dd', 'dt'),
				'dt' => array('dd', 'dt'),
				'rt' => array('rp', 'rt'),
				'rp' => array('rt', 'rp'),
				'option' => array('optgroup', 'option'),
				'td' => array('th', 'td'),
				'th' => array('td', 'th'),
				'tfoot' => array('tbody', 'tfoot'),
				'tbody' => array('tfoot', 'tbody'),
			);

		$noneEndTags = $this -> noneEndTags;
		$isTag = $this -> currentTagName($document, $pos);
		$i = 1;

		while(($pos = $this -> isPositionSearchBack($document, $pos, '>')) !== false) {
				$pos = $this -> isPositionSearchBack($document, $pos, '<');
				$tag = $this -> currentTagName($document, $pos);
				$noneEndTagFlug = false;
				$flug = false;

				for($j = 0, $count = count($noneEndTags); $j < $count; $j++) {
					if($noneEndTags[$j] === $tag) {
						$noneEndTagFlug = true;
					}
				}

				if($noneEndTagFlug === false) {
					if(substr($document, $pos, 2) === '</') {
						$i = $i + 1;
					}
					else{
						$i = $i - 1;
					}

					if($i === 0) {
						if(isset($omissionTags[$isTag])) {
							for($j = 0, $count = count($omissionTags[$isTag]); $j < $count; $j++) {
								if($omissionTags[$isTag][$j] === $tag) $flug = true;
							}
						}
						if($flug === true) {
							$i = 1;
						}else{
							if($option === 'position') return $pos;
							return $tag;
						}
					}
				}
		}
	}

	protected function parentNode($document, $pos) {
		$parentPos = $this -> parentTagName($document, $pos, 'position');
		return $this -> currentNode($document, $parentPos);
	}

	protected function isPositionSearchBack($document, $pos, $needle) {
		$pos = 
			strlen($document) - 
			strpos(
				strrev($document), 
				strrev($needle), 
				strlen($document) - $pos) - strlen($needle);
		if($pos < 0) {
			return false;
		}
		return $pos;
	}

	protected function currentNode($document, $pos = 0) {

		$tagName = $this -> currentTagName($document, $pos);
		$flag = false;

		for($i = 0, $count = count($this -> omissionTags); $i < $count; $i++) {
			if($tagName === $this -> omissionTags[$i]) {
				$flag = true;
				$i = $count;
			}
		}

		for($i = 0, $count = count($this -> noneEndTags); $i < $count; $i++) {
			if($this -> noneEndTags[$i] === $tagName) {
				$posTemp = strpos($document, '>', $pos);
				return array($pos, $posTemp + 1 - $pos, false);
			}
		}

		if($flag === true) {
			return $this -> parserAbnormal($document, $pos, $tagName);
		}else{
			return $this -> parserNormal($document, $pos, $tagName);
		}
	}

	protected function parserAbnormal($document, $pos, $name) {

		$posTemp = $pos + 1;
		$point = 1;
		$tagEndLen = 0;
		$endExsist = false;
		$flag = false;

		while(($posTemp = strpos($document, '<', $posTemp)) !== false) {

			if(substr($document, $posTemp + 1, 1) !== '/') {
				$tagName = $this -> currentTagName($document, $posTemp);
				for($i = 0, $count = count($this -> noneEndTags); $i < $count; $i++) {
					if($this -> noneEndTags[$i] === $tagName) $flag = true;
				}
				if(!isset($this -> omissionParentTags[$tagName]) && $flag === false) {
					$result = $this -> parserNormal($document, $posTemp, $tagName);
					$posTemp = $result[0] + $result[1];
					$posTemp = strpos($document, '<', $posTemp);
				}
			}

			for($i = 0, $count = count($this -> omissionParentTags[$name]); $i < $count; $i++) {
				if(strpos($document, '</'. $this -> omissionParentTags[$name][$i], $posTemp) === $posTemp) {
					$point = $point - 1;
					$i = $count;
				}
			}

			if(strpos($document, '<'. $name. ' ', $posTemp) === $posTemp) {
				$point = $point - 1;
			}
			else if(strpos($document, '</'. $name. '>', $posTemp) === $posTemp) {
				$point = $point - 1;
				$tagEndLen = strlen($name) + 3;
				$endExsist = true;
			}
			else if(strpos($document, '<'. $name. '>', $posTemp) === $posTemp) {
				$point = $point - 1;
			}
			else if(isset($this -> omissionPairTags[$name]) && strpos($document, '<'. $this -> omissionPairTags[$name]. '>', $posTemp) === $posTemp) {
				$point = $point - 1;
			}

			if($point === 0) return array($pos, $posTemp + $tagEndLen - $pos, $endExsist);

			$posTemp = $posTemp + 1;

		}
	}

	protected function parserNormal($document, $pos, $name) {

		$posTemp = $pos + 2;
		$point = 1;

		while(($posTemp = strpos($document, $name, $posTemp)) !== false) {

			if(strpos($document, '<'. $name. ' ', $posTemp - 1) === $posTemp - 1) {
				$point = $point + 1;
			}
			else if(strpos($document, '/'. $name. '>', $posTemp - 1) === $posTemp - 1) {
				$point = $point - 1;
			}
			else if(strpos($document, '<'. $name. '>', $posTemp - 1) === $posTemp - 1) {
				$point = $point + 1;
			}

			if($point === 0) return array($pos, $posTemp + strlen($name) + 1 - $pos, true);

			$posTemp = $posTemp + 1;

		}
	}

	protected function childNodes($document, $pos = 0) {
		$pos = $pos + 1;
		
		$result = array();
		while(($pos = strpos($document, '<', $pos)) !== false) {
			if(substr($document, $pos, 2) === '</') {
				return $result;
			}else{
				$node = $this -> currentNode($document, $pos);
				$result[] = $node;
				$pos = $node[0] + $node[1];
			}
		}
	}

	protected function getElementsByTag($document, $pos, $length, $name, $item) {
		$i = 0;
		$posDefault = $pos;

		if($this -> itemHistory < $item) {
			$i = $this -> itemHistory + 1;
			$pos = $this -> posHistory + 1;
		}

		while(($pos = strpos($document, $name, $pos)) !== false) {
			if($posDefault + $length < $pos) return array(0, 0, false);
			if(substr($document, $pos + strlen($name), 1) === ' ' || substr($document, $pos + strlen($name), 1) === '>') {
				if($i === $item) {
					$this -> itemHistory = $i;
					$this -> posHistory = $pos;
					return $this -> currentNode($document, $pos);
				}
				$i = $i + 1;
			}
			$pos = $pos + 1;
		}
	}

	protected function getElementByAttribute($document, $pos, $length, $name, $item, $attribute) {

		$i = 0;
		$limit = 50;
		$posDefault = $pos;
		$attributeLength = strlen($attribute. '="');
		if($this -> itemHistory < $item) {
			$i = $this -> itemHistory + 1;
			$pos = $this -> posHistory + 1;
		}

		while(($pos = strpos($document, $attribute. '="', $pos)) !== false) {
			if($posDefault + $length < $pos) return array(0, 0, false);
			$posTemp = strpos($document, $name, $pos);
			if(($posTemp - $pos) < $limit) {
				$flug = false;
				$result = 
					substr(
						$document, 
						$pos + $attributeLength, 
						strpos($document, '"', $pos + $attributeLength) - ($pos + $attributeLength)
					);
				$classes = $this -> split($result, ' ');
				for($j = 0, $count = count($classes); $j < $count; $j++) {
					if($classes[$j] === $name) $flug = true;
					$j = $count;
				}
				if($flug === true) {
					if($i === $item) {
						$this -> itemHistory = $i;
						$this -> posHistory = $pos;
						$posTemp = $this -> isPositionSearchBack($document, $pos, '<');
						return $this -> currentNode($document, $posTemp);
					}
					$i = $i + 1;
				}
				$pos = $pos + 1;
			}else{
				$pos = $posTemp - $limit;
			}
		}
	}

	protected function lengthAttribute($document, $pos, $name, $attribute) {
		$i = 0;
		$limit = 50;
		$attributeLength = strlen($attribute. '="');

		while(($pos = strpos($document, $attribute. '="', $pos)) !== false) {
			if($this -> pos + $this -> length < $pos) return $i;
			$posTemp = strpos($document, $name, $pos);
			if(($posTemp - $pos) < $limit) {
					$flug = false;
					$result = 
						substr(
							$document, 
							$pos + $attributeLength, 
							strpos($document, '"', $pos + $attributeLength) - ($pos + $attributeLength)
						);
					$classes = $this -> split($result, ' ');
					for($j = 0, $count = count($classes); $j < $count; $j++) {
						if($classes[$j] === $name) $flug = true;
						$j = $count;
					}
					if($flug === true) {
						$i = $i + 1;
					}
					$pos = $pos + 1;
			}else{
				$pos = $posTemp - $limit;
			}
		}
		return $i;
	}

	protected function getAttribute($document, $pos, $attribute) {
		$document = substr($document, $pos, strpos($document, '>', $pos) + 1 - $pos);

		if(($posTemp = strpos($document, $attribute. '="', 0)) !== false) {
			$attributeLength = strlen($attribute. '="');
			return substr(
				$document,
				$posTemp + $attributeLength, 
				strpos($document, '"', $posTemp + $attributeLength) - ($posTemp + $attributeLength)
			);
		}
		if(strpos($document, $attribute, 0) !== false) return true;
		return false;
	}

	protected function setAttribute($document, $pos, $attribute, $value) {
		
		$posTemp = strpos($document, '>', $pos);
		$documentTemp = substr($document, $pos, $posTemp - $pos);
		$posNew = 0;
		$posAttribute = strpos($documentTemp, ' '. $attribute, $posNew);
		if($posAttribute !== false) {
			$posAttributeEnd = strpos($documentTemp, '"', $posAttribute + strlen($attribute) + 3);
			if($posAttributeEnd === false) $posAttributeEnd = $posAttribute + strlen($attribute);
			$document = $this -> setDummy($document, $pos + $posAttribute, $pos + $posAttributeEnd - ($pos + $posAttribute) + 1);
		}

		if($value === false) return $document;
		if($value === true) $value = $attribute;

		$addStr = '';
		if(isset($this -> self -> exclusion[$posTemp])) $addStr = $this -> self -> exclusion[$posTemp];
		$addStr .= ' '. $attribute. '="'. $value. '"';
		$this -> self -> exclusion[$posTemp] = $addStr;

		return $document;
	}

	protected function setDummy($document, $pos, $length) {
		$dummy = '';
		for($i = 0, $count = $length; $i < $count; $i++) {
			$dummy .= ' ';
		}
		$document = $this -> deleteString($document, $pos, $length);
		$document = $this -> addString($document, $pos, $dummy);
		return $document;
	}

	protected function addString($document, $pos, $string) {
		$before = substr($document, 0, $pos);
		$after = substr($document, $pos);
		return $before. $string. $after;
	}

	protected function deleteString($document, $pos, $length) {
		$before = substr($document, 0, $pos);
		$after = substr($document, $pos + $length);
		return $before. $after;
	}
}
