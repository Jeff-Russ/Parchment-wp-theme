<?php
namespace Jr;

// class Exc extends Exception { }


class Codes {

	protected static $codes = null;
	protected static $user = ''
	protected static $lib = '';
	static function getCodes() { return self::$codes; }

	function __construct() {
		if (self::$codes===null) self::staticInit();

	}

	static function staticInit() {
		$incr = (int)(E_USER_NOTICE/5); $adder = $incr;
		define('USER_ERROR',  E_USER_ERROR + 1);
		define('LIB_ERROR',   E_USER_ERROR + (int)(E_USER_ERROR/2) );
		define('USER_WARN',   E_USER_WARNING + 1);
		define('LIB_WARN',    E_USER_WARNING + (int)(E_USER_WARNING/2) ); 
		define('USER_NOTICE', E_USER_NOTICE + 1);
		define('LIB_NOTICE',  E_USER_NOTICE + $adder); $adder += $incr;
		define('DEP_ERROR', E_USER_NOTICE + $adder); $adder += $incr;
		define('DEP_WARN',  E_USER_NOTICE + $adder); $adder += $incr;
		define('DEP_NOTICE',E_USER_NOTICE + $adder); $adder += $incr;
		self::$ranges = array(
			'all'=>array('min'=>USER_ERROR, 'max'=>E_STRICT-1),
			'3.00'=>array('min'=>USER_ERROR+1,'max'=>LIB_ERROR-1),
			'3.10'=>array('min'=>LIB_ERROR+1,'max'=>USER_WARN-1),
			'2.00'=>array('min'=>USER_WARN+1,'max'=>LIB_WARN-1),
			'2.10'=>array('min'=>LIB_WARN+1,'max'=>USER_NOTICE-1),
			'1.00'=>array('min'=>USER_NOTICE+1,'max'=>LIB_NOTICE-1),
			'1.10'=>array('min'=>LIB_NOTICE+1,'max'=>DEP_ERROR-1),
			'3.11'=>array('min'=>DEP_ERROR+1,'max'=>DEP_WARN-1),
			'2.11'=>array('min'=>DEP_WARN+1,'max'=>DEP_NOTICE-1),
			'1.11'=>array('min'=>DEP_NOTICE+1,'max'=>E_STRICT-1),
		);
		// 1.10  0    1      2       3  ... 6       7        8      9       
		// ^|    Info Notice Warning Error  
		//  |^   User Lib                   Runtime Compiler Parser Startup
		//    ^  ''   Deprecation                                   Option 
		define("NOTE", '1.0');
		define("WARN", '2.0');
		define("ERR",  '3.0');
		define("LIB",  '0.1');
		define("DEPR", '0.01');

		if (!defined('E_USER_DEPRECATED')) { define('E_USER_DEPRECATED', 16384); #ver5.3.0+
			if (!defined('E_RECOVERABLE_ERROR')) define('E_RECOVERABLE_ERROR', 4096); #v5.2.0+
		}
		// level parse:
		$lp = array(
			0=>array('Info', 'Notice', 'Warning', 'Error'),
			2=>array('User','Lib', 6=>'Runtime','Compiler','Parser','Startup'),
			3=>array('',    'Deprecation', 9=>'Option')
		);
		foreach (array(
			0 =>array('level'=>'0.00','const'=>''),# 0
			E_ERROR       =>array('const'=>'E_ERROR',  'level'=>'3.60'), # 1 RuntimeError
			E_WARNING     =>array('level'=>'2.60','const'=>'E_WARNING'), # 2 RuntimeWarning
			E_PARSE       =>array('level'=>'3.80','const'=>'E_PARSE'),   # 4 ParserError
			E_NOTICE      =>array('level'=>'1.70','const'=>'E_NOTICE'),  # 8 CompilerNotice
			E_CORE_ERROR  =>array('level'=>'3.90','const'=>'E_CORE_ERROR'),  # 16 StartupError
			E_CORE_WARNING=>array('level'=>'2.90','const'=>'E_CORE_WARNING'),# 32 StartupWarning
			E_COMPILE_ERROR  =>array('level'=>'3.70','const'=>'E_COMPILE_ERROR'), # 64 CompilerError
			E_COMPILE_WARNING=>array('level'=>'2.70','const'=>'E_COMPILE_WARNING'),# 128 CompilerWarning
			E_USER_ERROR  =>array('level'=>'3.00','const'=>'E_USER_ERROR'),  # 256  UserError
			USER_ERROR  =>array('level'=>'3.00','const'=>'USER_ERROR'),# 257
			LIB_ERROR   =>array('level'=>'3.10','const'=>'USER_ERROR'),# 384
			E_USER_WARNING=>array('level'=>'2.00','const'=>'E_USER_WARNING'),# 512  UserWarning
			USER_WARN   =>array('level'=>'2.00','const'=>'USER_WARN'),  # 513
			LIB_WARN    =>array('level'=>'2.10','const'=>'LIB_WARN'),  # 768
			E_USER_NOTICE =>array('level'=>'1.00','const'=>'E_USER_NOTICE'), # 1024 UserNotice
			USER_NOTICE =>array('level'=>'1.00','const'=>'USER_NOTICE'),# 1025
			LIB_NOTICE  =>array('level'=>'1.10','const'=>'LIB_NOTICE'), # 1228
			DEP_ERROR   =>array('level'=>'3.11','const'=>'DEP_ERROR'), # 1432
			DEP_WARN    =>array('level'=>'2.11','const'=>'DEP_WARN'),  # 1636
			DEP_NOTICE  =>array('level'=>'1.11','const'=>'DEP_NOTICE'), # 1840
			E_STRICT      =>array('level'=>'0.09','const'=>'E_STRICT'),# 2048 UserOption
			E_ALL         =>array('level'=>'0.09','const'=>'E_ALL'),# ???? UserOption
			E_RECOVERABLE_ERROR =>array(
				'level'=>'3.60','const'=>'E_RECOVERABLE_ERROR'),      # 4096 RuntimeError
			E_DEPRECATED =>array(
				'level'=>'2.61','const'=>'E_DEPRECATED'),  # 8192 RuntimeDeprecationWarning
			
			E_USER_DEPRECATED=>array('level'=>'3.01','const'=>'E_USER_DEPRECATED'), # 16384 UserDeprecationError
		) as $k=>$v) {
			// self::$codes[$k] = self::$defaults;
			self::$codes[$k]['code'] = $k;
			self::$codes[$k]['level'] = $v['level'];
			self::$codes[$k]['const'] = $v['const'];
			self::$codes[$k]['label'] = $lp[2][$v['level'][2]]
			                          . $lp[3][$v['level'][3]]
			                          . $lp[0][$v['level'][0]] . ": ";
		}
	}
	static function getSeverity($e_or_i, $as_int=false) {
		if ($e_or_i instanceof Exception) $code = $e_or_i->getCode();
		elseif (is_integer($e_or_i))      $code = $e_or_i;
		else return null;

		if ($code>=256) {
			if ($code<2048) {
				if    ($code<1024) $i = $code<512  ? 3:2;
				elseif($code<1636) $i = $code<1432 ? 2:3;
				else               $i = $code<1840 ? 2:1;
			} else {
				if ($code<8192) $i = $code<4096  ? 0:3;
				else            $i = $code<16384 ? 2:3;
			}
		} elseif ($code<0) $i = 0;
		elseif ($code<16) {
			if ($code<4)  $i = $code<2  ? 3:2;
			else          $i = $code<8  ? 3:1;
		}
		elseif ($code<64) $i = $code<32  ? 3:2;
		elseif ($code<256)$i = $code<128 ? 3:2;
		else              $t = $code<128 ? 3:2;

		if ($as_int) return $i;
		$sev = array (0=>'Const', 1=>'Notice', 2=>'Warning', 3=>'Error');
		return $sev[$i];
	}

	static function getNewCode($min_one_arg) {
		$argv = func_get_args(); $fn = "getNewCode() Argument Error:"; 
		$uncall = " 'actions' has un-callable";
		$whitelist = array('level'=>null,'label'=>null,'
			actions'=>null,'pop'=>null,'const'=>null,'message'=>null);
		$props = array();
		foreach ($argv as $v) {
			if (is_array($v)) {
				if (is_callable($a)) $props['actions'][] = $v;
				else {
					if ($dif=array_diff_key($v, $whitelist)) {
						$str = "invalid keys: "; foreach ($dif as $k=>$v) $str.="'$k', ";
						return self::trigger("$fn ".substr($str, 0, -2));
					}
					if (isset($v['actions'])) {
						foreach ($v['actions'] as $k=>$c) 
							if (!is_callable($c)) return self::trigger($fn.$uncall);
						$props['actions'] = $props['actions'] + $v['actions'];
						unset($v['actions']);
					}
					$props = $props + $v;
				}
			} elseif (($is_f=is_float($v)) || is_int($v)){
				$fmt = number_format($v,2);
				if (isset(self::$ranges[$fmt])) $props['level'] = $fmt;
				elseif (!$is_f)                      $props['pop'] = abs($v);
				else return self::trigger("$fn 'level'=>'$v'");
			} elseif (is_string($v)) {
				if     ($v==='')                          $props['label'] = $v;
				elseif (isset(self::$ranges[ $v ]))  $props['level'] = $v;
				elseif (is_callable( $v ))                $props['actions'][] = $v;
				elseif (preg_match('/^[A-Z0-9_]+$/',$v))  $props['const'] = $v;
				elseif (preg_match('/^[-+]{0,1}\d+$/',$v))$props['pop'] = abs($v);
				else                                      $props['message'] = $v;
			}else{return self::trigger("$fn type ".gettype($v)." invalid.")&&$f;}
		}
		# filter out some errors:
		if (!isset($props['level'])) return self::trigger("$fn requires 'level'");
		$ranges = self::$ranges; $level = $props['level'];
		for ($i=$ranges[$level]['min']; $i<=$ranges[$level]['max']; $i++) {
			if (!isset(self::$codes[$i])) { $props['code'] = $i; break; }
		}
		if (!isset($code)) return self::trigger("$fn failed to find a new code");

		if (isset($props['const'])) { $const = $props['const'];
			if (defined($props['const']) && isset(self::$codes[$props['const']]))
				return self::trigger("$fn '$props[const]'' already defined");
			define($const, $code);
		}
		if (isset($props['label'])) {
			if ($props['label']!=='' && ctype_alnum($props['label'][-1]))
				$props['label'] = "$props[label]: ";
		} else {
			$lp = self::$level_parse;
			$props['label'] = $lp[2][$level[2]].$lp[3][$level[3]].$lp[0][$level[0]].": ";
		}
		return $code;
	}
	static function removeCode($code) { unset(self::$codes[$code]); }

	static function console($data,$level='log'){

		if (is_string($level)) { $m = strtolower($level[0]);
			$m = $m==='e' ? 'error' : ($m==='2' ? 'warn' : 'log');
		} elseif (is_numeric($level)) {
			switch ($level) {
				case E_USER_NOTICE:  $m = 'log'; break;
				case E_USER_WARNING: $m = 'warn'; break;
				case E_USER_ERROR:   $m = 'error'; break;
				default: $m=$level<2?'log':($level>=3?'error':'warn');break;
			}
		} else $m = 'log';
		if (!($data instanceof Throwable) && !is_string($data))
			$data = to_string($data);
		echo "<script>console.$m('$data')</script>";
	}

	static function trigger($data, $level=E_USER_NOTICE) {
		if ($level!==E_USER_NOTICE&&$level!==E_USER_WARNING&&$level!==E_USER_ERROR) {
			if (is_string($level)) { $m = strtolower($level[0]);
				$m = $m==='e' ? E_USER_ERROR : (
					$m==='2' ? E_USER_WARNING : E_USER_NOTICE);
			} elseif (is_numeric($level)) {
				$m = $level<2? E_USER_NOTICE : (
					$level>=3 ? E_USER_ERROR : E_USER_WARNING);
			}
		}
		if (!($data instanceof Throwable) && !is_string($data))
			$data = to_string($data);
		trigger_error($data, $m);
		return false;
	}


	//// TOTALLY (used internally only) PROTECTED / PRIVATE //////

	protected static $defaults = array(
		'code'  => null,  'message'=> null, 'previous' => null, 'actions' => null,
		'level' =>'0.00', 'label'  => null, 'const'=> null, 'pop' => null,
		'trace' => null,  'line'   => null, 'file' => null, 'function'=> null,
		'class' => null,  'object' => null, 'type' => null, 'args' => null,
		'time'  => null,  'variable'=>null, 'value'=> null, # new trace-y things
	);
	protected static $ranges = null; # staticInit() fills this

	protected static $level_parse = array(
		0=>array('Info', 'Notice', 'Warning', 'Error'),
		2=>array('User','Lib', 6=>'Runtime','Compiler','Parser','Startup'),
		3=>array('',    'Deprecation', 9=>'Option')
	);
	# $level_parse means "level parse" and is used to get info and/or 
	# assemble a label from 'level', a floating point number
	protected static $C;

} Codes::staticInit();

// print_r(Codes::getCodes());
echo json_encode(Codes::getCodes(), JSON_PRETTY_PRINT);


// class Code extends \Exception {
// 	static function getLabel($level) {
// 		$l = number_format($level,2);
// 		$lp = self::$level_parse;
// 		return $lp[2][$l[2]].$lp[3][$l[3]].$lp[0][$l[0]].": ";
// 	}
// 	static function isInfo($level)   {return $level<1;}
// 	static function isNotice($level) {return $level>=1 && $level<2;}
// 	static function isWarning($level){return $level>=2 && $level<3;}
// 	static function isError($level)  {return $level>=3;}
// 	static function isUser($level)     {$l=number_format($level,2);return $l[2]==='0';}
// 	static function isDepecated($level){$l=number_format($level,2);return $l[2]==='1';}
// 	static function isOption($level)   {$l=number_format($level,2);return $l[2]==='9';}
// }
