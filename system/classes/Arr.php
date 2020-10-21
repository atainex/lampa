<?php

namespace Lampa;

class Arr
{
	public static function genOptions($default, $options) {
		foreach ($options as $k => $v) {
			$default[$k] = $v;
		}
		return $default;
	}

	public static function get($arr, $name, $default = null)
	{
		if (isset($arr[$name])) {
			if (empty($arr[$name])){
				return $default;
			}
			return $arr[$name];
		}
		return $default;
	}
	
	public static function selectOne($arr)
	{
		if (is_array($arr)) {
			foreach ($arr as $v) {
				return $v;
			}
		}
		return null;
	}
	
	public static function getByFormat($value, $options = []) {
		$options = self::genOptions(['scale_min' => 2, 'scale_max' => 2, 'cut_zero' => false], $options);
		$value = (string) round($value, $options['scale_max']);
		$value = (string) number_format($value, $options['scale_max'], '.', '');
		if (!empty($options['cut_zero'])) {
			if (stripos($value, '.')) {				
				$value = rtrim($value, '0');
			}
		}
		if (!stripos($value, '.')) {				
			$value .= '.';
		}
		while (strlen(explode('.', $value)[1]) < $options['scale_min']) {
			$value .= '0';
		}
		return rtrim($value, '.');
	}
	
	public function getRenderPercent($v, $p) {
		return $v * (100 + $p) / 100;
	}

}