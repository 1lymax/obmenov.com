<?php
################################################################################
#                                                                              #
# Webmoney Signer PHP edition by DKameleon (http://dkameleon.com)              #
#                                                                              #
# Updates and new versions: http://my-tools.net/wmxi/                          #
#                                                                              #
# Server requirements:                                                         #
# - MHash (not needed from 2007.04.06)                                         #
# - BCMath or GMP                                                              #
#                                                                              #
# History of changes:                                                          #
# 2007.02.14                                                                   #
# - initial release of the signer                                              #
# 2007.04.06                                                                   #
# - replaced MHash md4 with pure PHP md4                                       #
# 2007.08.26                                                                   #
# - added automatical switching between available MD4 implementations          #
# - added error messages                                                       #
# - added possibility to accept binary key data                                #
# 2007.04.06                                                                   #
# - added gmp support (by Alex Polushin)                                       #
# - improved _bcpowmod(), _md4(), _dec2hex(), _hex2dec() (by Alex Polushin)    #
# 2009.01.25                                                                   #
# - implemented backward compatibility for updated functions                   #
# - added internal debug option                                                #
# 2009.09.28                                                                   #
# - added $use_gmp flag, which set the use gmp_* functions (by Kirsanov Anton) #
#   gmp_* functions not correctly working on php 5.3/Centos                    #
#                                                                              #
################################################################################

//include_once("md4.php");

# WMSigner class
class WMSigner {

	var $wmid = "";
	var $pass = "";
	var $kwm = "";

	# backward compatibility switch
	var $old = false;

	# gmp math lib using
	var $use_gmp = false;

	# debug switch
	var $debug = false;

	# constructor
	function WMSigner($wmid, $pass, $kwm) {
		$this->wmid = $wmid;
		$this->pass = $pass;


		$this->kwm = file_exists($kwm) ? file_get_contents($kwm) : $kwm;
		if (strlen($this->kwm) != 164) {
			die("KWM file not found or KWM data invalid");
		}
	}


	# bcpowmod wrapper for old PHP
	function _bcpowmod($m, $e, $n) {
		if (function_exists("bcpowmod")) {
			return bcpowmod($m, $e, $n);
		}
		if (function_exists("gmp_powm") && $this->use_gmp) {
			return trim(gmp_strval(gmp_powm($m, $e, $n)));
		}

		# backward compatibility switch
		if ($this->old) {
			$r = "";
			while ($e != "0") {
				$t = bcmod($e, "4096");
				$r = substr("000000000000".decbin(intval($t)), -12).$r;
				$e = bcdiv($e, "4096");
			}
			$r = preg_replace("!^0+!", "", $r);
			if ($r == "") $r = "0";
			$m = bcmod($m, $n);
			$erb = strrev($r);
			$result = "1";
			$a[0] = $m;
			for ($i = 1; $i < strlen($erb); $i++) {
				$a[$i] = bcmod(bcmul($a[$i-1], $a[$i-1]), $n);
			}
			for ($i = 0; $i < strlen($erb); $i++) {
				if ($erb[$i] == "1") {
					$result = bcmod(bcmul($result, $a[$i]), $n);
				}
			}
			return $result;
		} else {
			$e = $this->_dec2hex($e);
			$a["0"] = "1";
			for($i = 1; $i < 16; $i++) {
				$a[dechex($i)] = bcmod(bcmul($a[dechex($i-1)], $m), $n);
			}
			$result = $a[$e{0}];
			$l = strlen($e);
			for($i = 1; $i < $l; $i++) {
				$result = bcmod(bcmul(bcpow($result, 16), $a[$e{$i}]), $n);
			}
			return $result;
		}

	}


	# md4 wrapper
	function _md4($data) {
		if (function_exists("mhash")) { return mhash(MHASH_MD4, $data); }

		if (function_exists("hash")) { return hash("md4", $data, true); }

		if (class_exists("MD4")) {
			$md = new MD4(true);
			return $md->Calc($data, true);
		}

		die("MD4 function not found.");
	}


	# XOR two strings
	function _XOR($str, $xor_str, $shift = 0) {
		$str_len = strlen($str);
		$xor_len = strlen($xor_str);
		$i = $shift;
		$k = 0;
		while ($i < $str_len) {
			$str{$i} = chr(ord($str{$i}) ^ ord($xor_str[$k]));
			$i++;
			$k++;
			if ($k >= $xor_len) { $k = 0; }
		}
		return $str;
	}


	# convert decimal to hexadecimal
	function _dec2hex($number) {
		if (function_exists("gmp_strval") && $this->use_gmp) {
			$hexval = trim(gmp_strval($number,16));
			if (strlen($hexval) % 2) { $hexval = "0".$hexval; }
			return $hexval;
		}

		# backward compatibility switch
		if ($this->old) {
			$hexvalues = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "A", "B", "C", "D", "E", "F");
			$hexval = "";
			while($number != "0") {
				$hexval = $hexvalues[bcmod($number, "16")].$hexval;
				$number = bcdiv($number, "16", 0);
			}
			if (strlen($hexval) % 2) { $hexval = "0".$hexval; }
			return $hexval;
		} else {
			$hexval = "";
			do {
				$hexval = dechex(substr($number, -8) % 256).$hexval;
				if (strlen($hexval) % 2) { $hexval = "0".$hexval; }
				$number = bcdiv($number, 256, 0);
			} while($number != "0");
			return $hexval;
		}

	}


	# convert hexadecimal to decimal
	function _hex2dec($number) {
		if (function_exists("gmp_strval") && $this->use_gmp) {
			return trim(gmp_strval("0x$number",10));
		}

		# backward compatibility switch
		if ($this->old) {
			$decvalues = array(
				"0" => "0", "1" => "1", "2" => "2", "3" => "3",
				"4" => "4", "5" => "5", "6" => "6", "7" => "7",
				"8" => "8", "9" => "9", "A" => "10", "B" => "11",
				"C" => "12", "D" => "13", "E" => "14", "F" => "15");
			$decval = "0";
			$number = strrev(strtoupper($number));
			for($i = 0; $i < strlen($number); $i++) {
				$decval = bcadd(bcmul(bcpow("16", $i, 0), $decvalues[$number{$i}]), $decval);
			}
			return $decval;
		} else {
			$n = strlen($number);
			$p = $n % 7;
			if ($p==0) $p = 7;
			$decval = hexdec(substr($number, 0, $p));
			while ($p < $n) {
				$decval = bcadd(bcmul($decval, 0x10000000), hexdec(substr($number, $p, 7)));
				$p += 7;
			}
			return $decval;
		}

	}


	# swap hexadecimal string
	function _shortunswap($hex_str) {
		$len = strlen($hex_str);
		if(($len / 2) % 2) {
			$hex_str = "00".$hex_str;
			$len = strlen($hex_str);
		}
		$pre_res = strrev($hex_str);
		$res = "";
		for($i = 0; $i < $len / 4; ++$i) {
			$res .= $pre_res[$i*4 + 3];
			$res .= $pre_res[$i*4 + 2];
			$res .= $pre_res[$i*4 + 1];
			$res .= $pre_res[$i*4 + 0];
		}
		return $res;
	}



	# both of SecureKeyByIDPW
	function SecureKeyByIDPW($wmid, $pass, $key_data, $half = false) {
		$idpw = $wmid;
		$pass_len = strlen($pass) >> ($half ? 1 : 0);
		$idpw .= substr($pass, 0, $pass_len);
		$digest = $this->_md4($idpw);
		$result = $key_data;
		$result["buf"] = $this->_XOR($result["buf"], $digest, 6);
		return $result;
	}


	# initializing E and N
	function Init($content, $key_data, &$keys) {
		if (strlen($content) < 24) { return 1; }
		if (strlen($content) - 8 < $key_data["len"]) { return -1; }

		$crc_cont = "";
		$crc_cont .= pack("v", $key_data["reserved"]);
		$crc_cont .= pack("v", $key_data["signflag"]);
		$crc_cont .= pack("V4", 0, 0, 0, 0);
		$crc_cont .= pack("V", $key_data["len"]);
		$crc_cont .= $key_data["buf"];
		$digest = $this->_md4($crc_cont);
		if (strcmp($digest, $key_data["crc"])) { return -1; }
		$tmp = unpack("Vreserved/vek_base", $key_data["buf"]);
		$tmp["buf"] = substr($key_data["buf"], 6);
		$keys["ekey"] = substr($tmp["buf"], 0, $tmp["ek_base"]);
		$tmp2 = unpack("vnk_base", substr($tmp["buf"], $tmp["ek_base"]));
		$tmp2["buf"] = substr($tmp["buf"], $tmp["ek_base"] + 2);
		$keys["nkey"] = substr($tmp2["buf"], 0, $tmp2["nk_base"]);
		return 0;
	}


	# sign data
	function Sign($data) {
		$result = "";
		$key_data = unpack("vreserved/vsignflag/a16crc/Vlen/a*buf", $this->kwm);
		$sign_keys = array();

		$key_test = $this->SecureKeyByIDPW($this->wmid, $this->pass, $key_data, true);

		$key_test["signflag"] = 0;
		if ($this->Init($this->kwm, $key_test, $sign_keys) != 0) {
			$key_test = $this->SecureKeyByIDPW($this->wmid, $this->pass, $key_data, false);

			$key_test["signflag"] = 0;
			if ($this->Init($this->kwm, $key_test, $sign_keys) != 0) {
				die("Checksum failed. KWM password invalid.");
			}
		}

		if (isset($sign_keys["ekey"]) && isset($sign_keys["nkey"])) {
			$digest = $this->_md4($data);
			$to_encrypt = $digest;
			for($i = 0; $i < 10; ++$i) { $to_encrypt .= pack("V", $this->debug ? 0 : mt_rand()); }
			$to_encrypt = pack("v", strlen($to_encrypt)).$to_encrypt;

			$m = $this->_hex2dec(bin2hex(strrev($to_encrypt)));
			$e = $this->_hex2dec(bin2hex(strrev($sign_keys["ekey"])));
			$n = $this->_hex2dec(bin2hex(strrev($sign_keys["nkey"])));

			$a = $this->_bcpowmod($m, $e, $n);
			$result = $this->_shortunswap($this->_dec2hex($a));
		}

		return strtolower($result);
	}


}
# WMSigner class


	# WMSigner caller
	function Sign($data, $wmid, $pass, $kwm) {
		$wmsigner = new WMSigner($wmid, $pass, $kwm);
		return $wmsigner->Sign($data);
	}

?>