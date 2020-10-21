<?php

namespace Lampa;

class Text
{
	
	public static function genCode($number = 12, $light = true, $onlymin = true)
	{
		if ($light) {
			if ($onlymin) {				
				$arr = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','r','s','t','u','v','x','y','z','1','2','3','4','5','6','7','8','9','0');
			} else {
				$arr = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','r','s','t','u','v','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','R','S','T','U','V','X','Y','Z','1','2','3','4','5','6','7','8','9','0');				
			}
		} else {
			$arr = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','r','s','t','u','v','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','R','S','T','U','V','X','Y','Z','1','2','3','4','5','6','7','8','9','0','.',',','(',')','[',']','!','?','&','^','%','@','*','$','<','>','/','|','+','-','{','}','`','~');
		}
		$pass = '';
		for($i = 0; $i < $number; $i++) {
			$index = mt_rand(0, count($arr) - 1);
			$pass .= $arr[$index];
		}
		return $pass;
	}
}