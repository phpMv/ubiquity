<?php
namespace Ubiquity\controllers\admin\utils;
use Ubiquity\utils\StrUtils;

class CodeUtils {
	public static function cleanParameters($parameters){
		$optional=false;
		$tmpResult=[];
		$params=\explode(",", $parameters);
		foreach ($params as $param){
			$param=\trim($param);
			@list($var,$value)=\explode("=", $param);
			if(isset($var) && isset($value)){
				$value=\trim($value);
				$var=self::checkVar($var);
				$tmpResult[]=$var.'='.$value;
				$optional=true;
			}elseif(isset($var)){
				$var=self::checkVar($var);
				if($optional)
					$tmpResult[]=$var."=''";
				else
					$tmpResult[]=$var;
			}
		}
		return \implode(',', $tmpResult);
	}

	public static function getParametersForRoute($parameters){
		$tmpResult=[];
		$params=\explode(",", $parameters);
		foreach ($params as $param){
			$param=\trim($param);
			@list($var,$value)=\explode("=", $param);
			if(isset($var) && isset($value)){
				break;
			}elseif(isset($var)){
				$var=self::unCheckVar($var);
				$tmpResult[]='{'.$var.'}';
			}
		}
		return $tmpResult;
	}

	public static function checkVar($var,$prefix='$'){
		if(StrUtils::isNull($var))
			return "";
		$var=\trim($var);
		if(!StrUtils::startswith($var, $prefix)){
			$var=$prefix.$var;
		}
		return $var;
	}

	public static function unCheckVar($var,$prefix='$'){
		if(StrUtils::isNull($var))
			return "";
			$var=\trim($var);
			if(StrUtils::startswith($var, $prefix)){
				$var=\substr($var, \sizeof($prefix));
			}
			return $var;
	}

	public static function indent($code,$count=2){
		$tab=\str_repeat("\t", $count);
		$lines=\explode("\n",$code);
		return $tab.\implode($tab, $lines);
	}

	public static function isValidCode($code){
		$temp_file = tempnam(sys_get_temp_dir(), 'Tux');
		$fp = fopen($temp_file, "w");
		fwrite($fp, $code);
		fclose($fp);
		$errors=exec('php -l '.$temp_file);
		\unlink($temp_file);
		if(strpos($errors, 'No syntax errors detected') === false){
			return false;
		}
		return true;
	}

}
