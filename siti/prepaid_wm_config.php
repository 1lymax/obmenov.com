<?
// $Id: wm_config.php,v 1.8 2006/01/20 19:32:32 asor Exp $
//
//            ****************************************************
//            ***   WM Shop Constant Information Section       ***
//            ****************************************************

//Public variables Section

$WM_SHOP_PURSE['WMU'] = 'U345709199686';   // Shop settlement purse
$WM_SHOP_PURSE['WMZ'] = 'Z126589890122';
$WM_SHOP_PURSE['WMR'] = 'R838048544492';
$WM_SHOP_PURSE['WME'] = 'E640544126053';
$WM_SHOP_WMID  = '219391095990';        // Shop WMID
$percent_addon=1.04;

//$WM_SHOP_PURSE_WMU = 'U323992523883';   // Shop settlement purse
//$WM_SHOP_PURSE_WMZ = 'Z264441197351';
//$WM_SHOP_PURSE_WMR = 'R412205642189';
//$WM_SHOP_PURSE_WME = 'E268491976829';
//$WM_SHOP_WMID  = '418941129503';        // Shop WMID
// Signature Section configuration 

$WM_WMSIGNER_PATH = '/home/user/sign/WMSigner';       //Path to WMSigner section. 
//  *********    Attention!!!!!! ***********
// 1. WMSigner should be located in a folder, where users will not be able to donwload it.
//   Same referrs to WMSigner.ini and key file.
// 2. WMSigner.ini conf searches for signer section at the same folder, where it is located itself.
// 3. WMSigner looks for keys file the path it is stated in WMSigner.ini, taking into account the fact that "current" 
//   folder is the one, where WMSigner script is located.
// For example:
// Scipts and html-documents folder: /home/my_site/html
// WMSigner folder: /home/my_site/sign
// Configuration file: /home/my_site/sign/WMSigner.ini :
//	123456789012
//	pass
//	/home/my_site/sign/keyfile.kwm
// (important: no spaces in the beginning of a string but obligatory LF at the end of the file!)
// For PHP+Apache for Windows (not checked!):
// $WM_WMSIGNER_PATH = 'd:\sign\WMSigner.exe';

$WM_CACERT = './WebMoneyCA.crt'; // WebMoney root certificate path, in PEM-format
//You can as well download this file at: https://www.wmcert.com/Cert/WebMoneyCA.crt

$LMI_MODE = '0';	// Payment request test mode. 
$LMI_SIM_MODE = '0';	// Extra field for test mode.
$LMI_SECRET_KEY = 'yfghfdktybt';// Secret Key, known to seller and WM Merchant Interface service only.
			       //  DO NOT FORGET TO CHANGE IT here and in merchant settings!
$LMI_HASH_METHOD = 'MD5'; // Method of forming control signature for  MD5|SIGN


?>