<?php
/*###############################################################################################
#	 ____     __                   ___    ______												#
#	/\  _`\  /\ \                /'___`\ /\__  _\												#
#	\ \ \L\ \\ \ \___    _____  /\_\ /\ \\/_/\ \/      ___      __								#
#	 \ \ ,__/ \ \  _ `\ /\ '__`\\/_/// /__  \ \ \     /'___\  /'__`\							#
#	  \ \ \/   \ \ \ \ \\ \ \L\ \  // /_\ \  \_\ \__ /\ \__/ /\ \L\ \							#
#	   \ \_\    \ \_\ \_\\ \ ,__/ /\______/  /\_____\\ \____\\ \___, \							#
#		\/_/     \/_/\/_/ \ \ \/  \/_____/   \/_____/ \/____/ \/___/\ \							#
#						   \ \_\                                   \ \_\						#
#							\/_/                                    \/_/						#
#																								#
#																								#
# written by Alexander Theiﬂen <alex.theissen@gmail.com>										#
# Website: http://devmania.de																	#
# SF Project Page: http://sourceforge.net/projects/php2icq										#
#																								#
# This file is licensed under the GPL license													#
################################################################################################*/

/**
* Main Class File
*
* It contains the class with all the functions to communicate with
* the icq server.
*
* 
* @package classlib
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @author Alexander Theiﬂen
*/


//******************************************************
// Some defines used for describing the FLAP-Channel
//******************************************************
define('FLAP_CHANNEL_NEW_CONNECTION_NEGOTIATION'		, 0x01);
define('FLAP_CHANNEL_SNAC_DATA'							, 0x02);
define('FLAP_CHANNEL_LEVEL_ERROR'						, 0x03);
define('FLAP_CHANNEL_CLOSE_CONNECTION_NEGOTIATION'		, 0x04);
define('FLAP_CHANNEL_KEEP_ALIVE'						, 0x05);


//******************************************************
// Online Status defines       
//******************************************************
define('STATUS_WEBAWARE',	0x0001);	// Status webaware flag 
define('STATUS_SHOWIP',		0x0002);	// Status show ip flag 
define('STATUS_BIRTHDAY',	0x0008);	// User birthday flag 
define('STATUS_WEBFRONT',	0x0020);	// User active webfront flag 
define('STATUS_DCDISABLED', 0x0100);	// Direct connection not supported 
define('STATUS_DCAUTH',		0x1000);	// Direct connection upon authorization 
define('STATUS_DCCONT',		0x2000);	// DC only with contact users


define('STATUS_ONLINE',		0x0000);	// Status is online 
define('STATUS_AWAY',		0x0001);	// Status is away 
define('STATUS_DND',		0x0002);	// Status is no not disturb (DND); 
define('STATUS_NA',			0x0004);	// Status is not available (N/A); 
define('STATUS_OCCUPIED',	0x0010);	// Status is occupied (BISY); 
define('STATUS_FREE4CHAT',	0x0020);	// Status is free for chat 
define('STATUS_INVISIBLE',	0x0100);	// Status is invisible



/**
* The main class
* 
* It contains all the functions to communicate with
* the icq server.
*
* @package classlib
*/
class php2icq
{
	/**
	* Icq Uin/Account
	* 
	* The icq username/number/email to connect.
	*
	* @access private
	* @var string
	*/
	var $uin;

	/**
	* Account password
	* 
	* This is the password used to login into the mentioned account.
	*
	* @access private
	* @var string
	*/
	var $pass;

	/**
	* Current connection handel
	* 
	* All functions use this socket handle to send/read
	* the server data.
	*
	* @access private
	* @var int
	*/
	var $conn;
	
	/**
	* FLAP sequence id
	* 
	* This var contains the current sequence id. The sequence
	* id sits in the FLAP header and get incremented by every send
	* procedure. This is done in {@link php2icq::add_flap_header()}.
	*
	* @access private
	* @var int
	*/
	var $sequence_id;

	/**
	* Errorstring
	* 
	* If a function failed (only the public functions),
	* then the errorstring get filled with a usefull errormessage.
	* The user may call {@link php2icq::get_error()} to get
	* the error message.
	*
	* @access private
	* @var string
	*/
	var $error;

	/**
	* Loginerror allocation
	* 
	* When we send the auth packet, the server may send
	* an error packet. There are several possibilities what is happended then.
	*
	* @access private
	* @var array
	*/
	var $login_errno_arr;

	/**
	* Supportet SNAC families
	* 
	* Contains the SNAC families supportet by the server. 
	* The associated versions are stored in annother array.
	*
	* @access private
	* @var array
	* @see php2icq::$snac_versions
	*/
	var $snac_families;

	/**
	* Supportet SNAC versions
	* 
	* Contains the versions of the SNAC families supportet by the server. 
	* The associated familie names are stored in annother array.
	*
	* @access private
	* @var array
	* @see php2icq::$snac_families
	*/
	var $snac_versions;

	/**
	* The used uin online status
	* 
	* You can choose your status from the following list (only one):<br />
	* STATUS_ONLINE<br />
	* STATUS_AWAY<br />
	* STATUS_DND<br />
	* STATUS_NA<br />
	* STATUS_OCCUPIED<br />
	* STATUS_FREE4CHAT<br />
	* STATUS_INVISIBLE<br />
	*
	* @access private
	* @var int
	*/
	var $online_status;




	//###########################################################################################################################################
	//
	// Private Methods (should not be called from outside of the class)
	//
	//###########################################################################################################################################
	/**
	* Creates error strings for the exspected login errors.
	* 
	* If the authentication fails the server sends an error
	* TLV containing one of these error codes. This array
	* is later used to identify the error codes and give the user
	* some usefull information.
	*
	* @access private
	* @see php2icq::login()
	*/
	function create_login_errno_arr()
	{
		$this->login_errno_arr[0x0001] = 'Invalid nick or password';
		$this->login_errno_arr[0x0002] = 'Service temporarily unavailable';
		$this->login_errno_arr[0x0003] = 'All other errors';
		$this->login_errno_arr[0x0004] = 'Incorrect nick or password, re-enter';
		$this->login_errno_arr[0x0005] = 'Mismatch nick or password, re-enter'; 
		$this->login_errno_arr[0x0006] = 'Internal client error (bad input to authorizer)';
		$this->login_errno_arr[0x0007] = 'Invalid account';
		$this->login_errno_arr[0x0008] = 'Deleted account'; 
		$this->login_errno_arr[0x0009] = 'Expired account'; 
		$this->login_errno_arr[0x000A] = 'No access to database';
		$this->login_errno_arr[0x000B] = 'No access to resolver'; 
		$this->login_errno_arr[0x000C] = 'Invalid database fields'; 
		$this->login_errno_arr[0x000D] = 'Bad database status';
		$this->login_errno_arr[0x000E] = 'Bad resolver status';
		$this->login_errno_arr[0x000F] = 'Internal error';
		$this->login_errno_arr[0x0010] = 'Service temporarily offline';
		$this->login_errno_arr[0x0011] = 'Suspended account';
		$this->login_errno_arr[0x0012] = 'DB send error';
		$this->login_errno_arr[0x0013] = 'DB link error';
		$this->login_errno_arr[0x0014] = 'Reservation map error';
		$this->login_errno_arr[0x0015] = 'Reservation link error';
		$this->login_errno_arr[0x0016] = 'The users num connected from this IP has reached the maximum';
		$this->login_errno_arr[0x0017] = 'The users num connected from this IP has reached the maximum (reservation)';
		$this->login_errno_arr[0x0018] = 'Rate limit exceeded (reservation). Please try to reconnect in a few minutes';
		$this->login_errno_arr[0x0019] = 'User too heavily warned';
		$this->login_errno_arr[0x001A] = 'Reservation timeout';
		$this->login_errno_arr[0x001B] = 'You are using an older version of ICQ. Upgrade required';
		$this->login_errno_arr[0x001C] = 'You are using an older version of ICQ. Upgrade recommended';
		$this->login_errno_arr[0x001D] = 'Rate limit exceeded. Please try to reconnect in a few minutes';
		$this->login_errno_arr[0x001E] = 'Can\'t register on the ICQ network. Reconnect in a few minutes';
		$this->login_errno_arr[0x0020] = 'Invalid SecurID';
		$this->login_errno_arr[0x0022] = 'Account suspended because of your age (age < 13)';
		$this->login_errno_arr[0xFFFF] = 'Wrong errornumber format';
	}


	/**
	* Opens socket-connection to specified server.
	* 
	* The Connection handler will be stored to a class var. The method
	* only return true or false (success or failure). The FLAP sequence id, which
	* is incremented for every send process get reseted.
	*
	* @param string The ip or url to connect to
	* @param int The port to connect to
	* @return bool True if successfully. False if connection failed (e.g. due timeout).
	* @access private
	* @see php2icq::$con
	* @see php2icq::close_connection()
	*/
	function open_connection($addr, $port)
	{
		@$this->conn = fsockopen($addr, $port, $errno, $errstring, 120);
		
		$this->sequence_id = 0;

		If (!$this->conn)
		{
			$this->error = '<b>ERROR</b>: Connection to <b>'.$addr.'</b> on port <b>'.$port.'</b> failed.';
			return false;
		}
		return true;
	}


	/**
	* Closes connection using current server handle.
	* 
	* Nothing else than fclose(). Please use this function to close the connection.
	*
	* @return bool True if successfully. False if connection handle is invalid.
	* @access private
	* @see php2icq::$con
	* @see php2icq::open_connection()
	*/
	function close_connection()
	{
		if ($this->conn)
		{
			fclose($this->conn);
			return true;
		}
		return false;
	}

	/**
	* Writes data to the current TCP stream.
	* 
	* Nothing else than fwrite(). Please use this function to write.
	*
	* @param mixed The data which will be send.
	* @access private
	*/
	function fsend($what)
	{
		fwrite($this->conn, $what);
	}


	/**
	* Splits a number into chosen byte count.
	* 
	* Splits a number into the count of the
	* chosen bytes and save it to a string.
	* This function is mainly used to fit the protocoll.
	*
	* @param int The number to split
	* @param int The resulting byte count (In how many pieces the number get splitted)
	* @return mixed If the method was successfully it returns a string with the splitted number-bytes.
	* False if the number is to high for the chosen byte count.
	* @access private
	*/
	function split_byte($number, $bytes)
	{
		// If the number cannot be displayed with the chosen byte count
		if ($number >= pow(0x100, $bytes))
		{
			return false;
		}
		
		$splitted_bytes = '';	// the number will be saved as a string (for sending)
		
		for ($i = 1; $i <= $bytes; $i++)
		{		
			// Zuerst bestimmen wir den Divisor f¸r die aktuelle Stelle der Zahl
			$power = pow(0x100, $bytes - $i);
					
			$full = (int) ($number / $power);
			$splitted_bytes .= chr($full);

			// Den Rest nehmen wir als neuen Hex-Wert f¸r die n‰chste Schleife  
			$number = $number - ($full * $power);
		}
	return $splitted_bytes;
	}


	/**
	* Adds the FLAP header to a packet body.
	* 
	* The method is very usefull, because you only have to care about
	* the body of a packet. If you have SNAC data in your packet call
	* {@link php2icq::add_snac_header()}. <--- This method will add 
	* the FLAP header, too (and SNAC of course ;)). This method also increments the FLAP sequence id.
	*
	*
	* @param string The packet body
	* @param int The FLAP channel which will be used
	* @return string A complettly sending reading string
	* @access private
	* @see php2icq::add_snac_header(), php2icq::make_tlv()
	*/
	function add_flap_header($flap_body, $channel)
	{
		//flap header
		$flap =
		chr(0x2A).									//FLAP id byte
		chr($channel).								//FLAP channel
		$this->split_byte($this->sequence_id, 2).	//FLAP datagram seq number
		$this->split_byte(strlen($flap_body), 2).	//FLAP data size
		$flap_body									//FLAP data
		;
		
		// For every send-process the sequence id has to be incremented
		$this->sequence_id++;
		
		return $flap;
	}


	/**
	* Adds the SNAC & FLAP header to a packet body.
	* 
	* The method is very usefull, because you only have to care about
	* the body of a packet. It will first add SNAC header and then 
	* {@link php2icq::add_flap_header()}. If you have only FLAP data (without a SNAC)
	* just call {@link php2icq::add_flap_header()}.
	*
	*
	* @param string The packet body
	* @param int The SNAC family
	* @param int The SNAC sub-family
	* @param int The SNAC request id (currently not in use, set it to 0)
	* @param int The SNAC flags
	* @return string A completly sending reading string
	* @access private
	* @see php2icq::add_flap_header(), php2icq::make_tlv()
	*/
	function add_snac_header($snac_body, $snac_fam, $snac_sub, $snac_request_id, $snac_flags = 0x0000)
	{
		$snac =
		$this->split_byte($snac_fam, 2).		//Family (service) id number
		$this->split_byte($snac_sub, 2).		//Family subtype id number
		$this->split_byte($snac_flags, 2).		//SNAC flags
		$this->split_byte($snac_request_id, 4).	//SNAC request id
		$snac_body;								//SNAC data
		;
		
		$packet = 
		$this->add_flap_header($snac, 0x02)		//FLAP Header
		;
		
		return $packet;
	}

	/**
	* Makes a TLV from the given parameters
	* 
	* You only have to enter the 2 parameters and you get
	* one TLV string easy to use. Simply add it to your current
	* packet body.
	*
	*
	* @param int The TLV identifier
	* @param string The content of the TLV (uin, password, email, search strings...)
	* @param int Is the TLV content a defined count of bytes? If true enter the byte count here. If its a string enter 0.
	* @return string The merged TLV
	* @access private
	* @see php2icq::add_flap_header(), php2icq::add_snac_header()
	*/
	function make_tlv($tlv_id, $tlv_data, $tlv_bytes)
	{
		// if tlv data is a number
		if ($tlv_bytes > 1)
		{
			$tlv_data = $this->split_byte($tlv_data, $tlv_bytes);
		}

		// a tlv
		$tlv =
		$this->split_byte($tlv_id, 2).				//TLV id word
		$this->split_byte(strlen($tlv_data), 2).	//TLV data length
		$tlv_data									//TLV data
		;
		
		return $tlv;
	}


	/**
	* Encodes the password.
	* 
	* Takes the clear password string and encodes it with
	* the given array.
	* The icq server only accepts the encoded password.
	*
	* @param string The clear password to encode
	* @return string The encoded password string
	* @access private
	*/
	function roast_password($pw)
	{
		$roast = array(0xF3, 0x26, 0x81, 0xC4, 0x39, 0x86, 0xDB, 0x92, 0x71, 0xA3, 0xB9, 0xE6, 0x53, 0x7A, 0x95, 0x7C);
		
		$roastet_password = '';
		
		for ($i = 0; $i < strlen($pw); $i++)
		{
			$roastet_password .= chr($roast[$i] ^ ord(substr($pw, $i, 1)));
		}
		
		return $roastet_password;
	}


	/**
	* Reads one byte from the data stream and casts it into
	* an integer value.
	* 
	* The red bytes are merged into one value so they are
	* easy to use.
	*
	* @return int The resulting int value
	* @access private
	* @see php2icq::fget_word(), php2icq::fget_dword(), php2icq::fget_string(), php2icq::fget_dummy_bytes(), php2icq::fget_dummy_packet()
	*/
	function fget_byte()
	{
		$zeichen = fread($this->conn, 1);
		return ord($zeichen);
	}


	/**
	* Reads two bytes from the data stream and casts it into
	* an integer value.
	* 
	* The red bytes are merged into one value so they are
	* easy to use.
	*
	* @return int The resulting int value
	* @access private
	* @see php2icq::fget_byte(), php2icq::fget_dword(), php2icq::fget_string(), php2icq::fget_dummy_bytes(), php2icq::fget_dummy_packet()
	*/
	function fget_word()
	{
		$byte_m = ord(fread($this->conn, 1));
		$byte_l = ord(fread($this->conn, 1));
		return (0x100 * $byte_m) + $byte_l;
	}


	/**
	* Reads four bytes from the data stream and casts it into
	* an integer value.
	* 
	* The red bytes are merged into one value so they are
	* easy to use.
	*
	* @return int The resulting int value
	* @access private
	* @see php2icq::fget_byte(), php2icq::fget_word(), php2icq::fget_string(), php2icq::fget_dummy_bytes(), php2icq::fget_dummy_packet()
	*/
	function fget_dword()
	{
		$byte_1 = ord(fread($this->conn, 1));
		$byte_2 = ord(fread($this->conn, 1));
		$byte_3 = ord(fread($this->conn, 1));
		$byte_4 = ord(fread($this->conn, 1));
		return (0x1000000 * $byte_1) + (0x10000 * $byte_2) + (0x100 * $byte_3) + $byte_4;
	}


	/**
	* Reads multiple bytes from the datastream and saves
	* them as a string.
	* 
	* The red bytes are simply saved as a string. This function
	* is usefull for reading (ascii) text, but not for numbers you want to operate with.
	*
	* @param int How many bytes will be read
	* @return string The resulting data string
	* @access private
	* @see php2icq::fget_byte(), php2icq::fget_word(), php2icq::fget_dword(), php2icq::fget_dummy_bytes(), php2icq::fget_dummy_packet()
	*/
	function fget_string($anzahl_bytes)
	{
		$zeichen = '';
		for ($i = 0; $i < $anzahl_bytes; $i++)
		{
				$zeichen .= fread($this->conn, 1);
		}
		return $zeichen;
	}

	
	/**
	* Reads multiple bytes from the datastream without
	* saving them anywhere.
	* 
	* Usefull for moving the data pointer.
	*
	* @param int How many bytes will be overread
	* @access private
	* @see php2icq::fget_byte(), php2icq::fget_word(), php2icq::fget_dword(), php2icq::fget_string(), php2icq::fget_dummy_packet()
	*/
	function fget_dummy_bytes($anzahl_bytes)
	{
		for ($i = 0; $i < $anzahl_bytes; $i++)
		{
			$zeichen .= fread($this->conn, 1);
		}
	}


	/**
	* Simply overreads a whole packet.
	* 
	* Usefull for moving the data pointer.
	*
	* @param int How many bytes will be overread
	* @return bool True if success. False if failure (eg. Unexpected bytes etc.).
	* @access private
	* @see php2icq::fget_byte(), php2icq::fget_word(), php2icq::fget_dword(), php2icq::fget_string(), php2icq::fget_dummy_bytes()
	*/
	function fget_dummy_packet($flap_channel)
	{
		// Proof FLAP header and get length of the body
		if (!($flap_length = $this->check_flap_header($flap_channel)))
		{
			return false;
		}

		// Overread the whole body
		$this->fget_dummy_bytes($flap_length);

		return true;
	}


	/**
	* Reads the FLAP header an check it for validity
	* 
	* You don't have to care about the FLAP header anymore. When you read
	* a packet just call this function first.
	*
	* @param int The exspected FLAP header
	* @return mixed Length of the FLAP body as int or false if failed
	* @access private
	* @see php2icq::fget_byte(), php2icq::fget_word(), php2icq::fget_dword(), php2icq::fget_string(), php2icq::fget_dummy_bytes(), php2icq::fget_dummy_packet()
	*/
	function check_flap_header($flap_channel)
	{
		// Proof flap-id
		if ($this->fget_byte() != 0x2a)
		{
			$this->error = '<b>ERROR</b>: Unexpected FLAP packet start-byte';
			$this->close_connection();
			return false;
		}
		// Channel must be the chosen one
		if ($this->fget_byte() != $flap_channel)
		{
			$this->error = '<b>ERROR</b>: Unexpected FLAP packet channel';
			$this->close_connection();
			return false;
		}
		// Overread Seq.-No
		$this->fget_dummy_bytes(2);
		// Read the length of the FLAP packet
		$flap_length = $this->fget_word();

		return $flap_length;
	}

	/**
	* Reads the SNAC header an check it for validity and returns some info about the SNAC
	* 
	* You don't have to care about the SNAC header anymore. When you read
	* a packet just call this function first. It will return all information need
	* about the SNAC packet.
	*
	* @param int The exspected SNAC-Family
	* @param int The exspected SNAC-Sub-Family
	* @return mixed Length of the FLAP body as int or false if failed
	* <br />The returned array contains:
	* <br />
	* [0] = SNAC flags <br />
	* [1] = SNAC request id 
	* @access private
	* @see php2icq::fget_byte(), php2icq::fget_word(), php2icq::fget_dword(), php2icq::fget_string(), php2icq::fget_dummy_bytes(), php2icq::fget_dummy_packet(),
	* php2icq::check_flap_header()
	*/
	function check_snac_header($snac_fam, $snac_sub)
	{
		// the returned info array
		$snac_info = array();

		// Proof SNAC fam
		if ($this->fget_word() != $snac_fam)
		{
			$this->error = '<b>ERROR</b>: Unexpected SNAC Family';
			$this->close_connection();
			return false;
		}
		// Proof SNAC sub-fam
		if ($this->fget_word() != $snac_sub)
		{
			$this->error = '<b>ERROR</b>: Unexpected SNAC Sub-Family';
			$this->close_connection();
			return false;
		}
		// read flags
		$snac_info[0] = $this->fget_word();
		// read request idea
		$snac_info[1] = $this->fget_dword();

		return $snac_info;
	}
	
	//###########################################################################################################################################
	//
	// Public Methods (this methods can be uses from the otside of the class)
	//
	//###########################################################################################################################################
	/**
	* The Constructor
	* 
	* It does some simple construction tasks.
	*
	* @param string The username/icqnumber to login.
	* @param string The password used to login.
	* @param int The status the used uin will have when you login. These are the available staus commands:
	* STATUS_ONLINE<br />
	* STATUS_AWAY<br />
	* STATUS_DND<br />
	* STATUS_NA<br />
	* STATUS_OCCUPIED<br />
	* STATUS_FREE4CHAT<br />
	* STATUS_INVISIBLE<br />
	* @access public
	*/
	function php2icq($uin, $pass, $online_status)
	{
		$this->uin = $uin;
		$this->pass = $pass;
		$this->online_status = $online_status;

		$this->create_login_errno_arr();
	}


	/**
	* Authentication & Login to the main server
	* 
	* It uses the stored uin & password to pass the authentication.
	* After that it connects to the icq main server and prepare
	* the connection for further use. If an error occured you may
	* use {@link php2icq::get_error()} to get information about the error.
	*
	* @return bool True if success. False if an error occured.
	* @access public
	*/
	function login()
	{
		//####################################################################
		//
		// Login stage I: Channel 0x01 authorization
		//
		//####################################################################
		//******************************************************
		//  Connect to the authserver
		//******************************************************
		if (!$this->open_connection('login.icq.com', 5190))
		{
			return false;
		}


		//******************************************************
		// Send: CLI_IDENT  
		//******************************************************
		// Client properties 
		$client_name = 'Php2Icq, 0.1.0';  // Plz do not change. Its usefull to spread php2icq.
		$client_id = 54752;
		$client_major_version = 0;
		$client_minor_version = 1;
		$client_lesser_version = 0;
		$client_build_version = 0;
		$client_distribution_number = 5471;
		$client_language = 'en';  // you can change this
		$client_country = 'en';   // you can change this

		// flap body
		$auth =
		$this->split_byte(0x00000001, 4).								//protocol version number
		$this->make_tlv(0x0001, $this->uin, 0).							//screen name (uin)
		$this->make_tlv(0x0002, $this->roast_password($this->pass), 0).	//roasted password
		$this->make_tlv(0x0003, $client_name, 0).						//client id string
		$this->make_tlv(0x0016, $client_id, 2).							//client id
		$this->make_tlv(0x0017, $client_major_version, 2).				//client major version
		$this->make_tlv(0x0018, $client_minor_version, 2).				//client minor version
		$this->make_tlv(0x0019, $client_lesser_version, 2).				//client lesser version
		$this->make_tlv(0x001A, $client_build_version, 2).				//client build number
		$this->make_tlv(0x0014, $client_distribution_number, 4).		//distribution number
		$this->make_tlv(0x000F, $client_language, 0).					//client language (2 symbols)
		$this->make_tlv(0x000E, $client_country, 0)						//client country (2 symbols)
		;
		
		$this->fsend($this->add_flap_header($auth, 0x01));


		//******************************************************
		// Overread: standart ACK packet
		//******************************************************
		if (!$this->fget_dummy_packet(FLAP_CHANNEL_NEW_CONNECTION_NEGOTIATION))
		{
			return false;
		}


		//******************************************************
		// Read: SRV_COOKIE 
		//******************************************************
		// Check FLAP Header
		if (!($flap_length = $this->check_flap_header(FLAP_CHANNEL_CLOSE_CONNECTION_NEGOTIATION)))
		{
			return false;
		}
		// Read all TLV's
		while ($flap_length > 0)
		{
			// Read TLV id
			$Value_id = $this->fget_word();
			// Read length of the TLV value
			$Value_length = $this->fget_word();
			// We are just looking for some special TLV's
			if ($Value_id == 0x0005) // BOS Server ID
			{
				$BOS_server_ip = $this->fget_string($Value_length);
			} 
			elseif ($Value_id == 0x0006) // Authorisation Cookie
			{
				$Auth_cookie = $this->fget_string($Value_length);
			}
			elseif ($Value_id == 0x0008) // Login Error
			{
				//******************************
				// login error occurred
				//******************************
				if ($Value_length == 2) // error number should be 2 bytes
				{
					$login_errno = $this->fget_word(); // get error number from tlv
				}
				else
				{
					$login_errno = 0xFFFF;	// set it to unknown error format if the errno is not 2 bytes
				}	
				if (isset($this->login_errno_arr[$login_errno]))	// check if it is a known error
				{
					$login_error_string = $this->login_errno_arr[$login_errno];
				}
				else
				{
					$login_error_string = 'Unknown Error';
				}
				$this->error = '<b>ERROR</b>: Login failed. <b>ERRNO</b>: '.dechex($login_errno).'(Hex) <b>ERRORSTRING</b>: '.$login_error_string.'.';
				$this->close_connection();
				return false;
			}
			else
			{	
				// Overread the other crap
				$this->fget_dummy_bytes($Value_length);
			}
			$flap_length -= (4 + $Value_length);
		}
		
		
		// Disconnect form Auth Server
		$this->close_connection();


		//####################################################################
		//
		// Login stage II: Protocol negotiation
		//
		//####################################################################
		//******************************
		// connect to BOS server
		//******************************
		// Seperate ip from port number
		$BOS_array = explode(':', $BOS_server_ip);
		//Initiate Connection to BOS Server
		if (!$this->open_connection($BOS_array[0], $BOS_array[1]))
		{
			return false;
		}
		

		//******************************
		// Send: CLI_COOKIE
		//******************************
		//flap data
		$auth_cookie =
		$this->split_byte(0x01, 4).																	//protocol version number
		$this->split_byte(0x06, 2).$this->split_byte(strlen($Auth_cookie), 2).$Auth_cookie			//Auth Cookie
		;
		
		$this->fsend($this->add_flap_header($auth_cookie, 0x01)); 


		//******************************************************
		// Overread: ACK packet
		//******************************************************
		if (!$this->fget_dummy_packet(FLAP_CHANNEL_NEW_CONNECTION_NEGOTIATION))
		{
			return false;
		}
	
		
		//******************************************************
		// Overread: SNAC(01,03)  SRV_FAMILIES
		//******************************************************
		if (!$this->fget_dummy_packet(FLAP_CHANNEL_SNAC_DATA))
		{
			return false;
		}

	
		//******************************************************
		// Send: SNAC(01,17)  CLI_FAMILIES_VERSIONS
		// we send the families incl. versions which are
		// supported/used by this lib
		// see SNAC(01,02)
		//******************************************************
		//snac data
		$snac_body = $this->split_byte(0x0001, 2).$this->split_byte(0x0004, 2).		// Generic service controls v4
		$this->split_byte(0x0004, 2).$this->split_byte(0x0001, 2)					// ICBM (messages) service  v1
		;
		
		$this->fsend($this->add_snac_header($snac_body, 0x0001, 0x0017, 0));
		

		//******************************************************
		// Read: SNAC(01,18)  SRV_FAMILIES_VERSIONS   
		//******************************************************
		// Check FLAP Header
		if (!($flap_length = $this->check_flap_header(FLAP_CHANNEL_SNAC_DATA)))
		{
			return false;
		}
		// Check FLAP Header
		if (!($this->check_snac_header(0x0001, 0x0018)))
		{
			return false;
		}
		// Read all supported families
		$flap_length -= 10;
		while ($flap_length > 0)
		{
			$this->snac_families[] = $this->fget_word($BOS);
			$this->snac_versions[] = $this->fget_word($BOS);
			$flap_length -= 4;
		}


		//******************************************************
		// Send: SNAC(01,06)  CLI_RATES_REQUEST     
		//******************************************************
		$snac_body = '';
		$this->fsend($this->add_snac_header($snac_body, 0x0001, 0x0006, 0));


		//******************************************************
		// Read: SNAC(01,07)  SRV_RATE_LIMIT_INFO   
		// --------
		// we do not need this information, yet
		// this will be implemented later
		//******************************************************
		// Check FLAP Header
		if (!($flap_length = $this->check_flap_header(FLAP_CHANNEL_SNAC_DATA)))
		{
			return false;
		}
		// Check FLAP Header
		if (!($this->check_snac_header(0x0001, 0x0007)))
		{
			return false;
		}
		// Read number of rate classes
		$rate_classes = $this->fget_word();
		// Overread the rest
		$this->fget_dummy_bytes($flap_length - 12);


		//******************************************************
		// Send: SNAC(01,08)  CLI_RATES_ACK        
		//******************************************************
		$snac_body = '';
		for ($i = 1; $i <= $rate_classes; $i++)
		{
			$snac_body .= $this->split_byte($i, 2);
		}

		$this->fsend($this->add_snac_header($snac_body, 0x0001, 0x0008, 0));


		//####################################################################
		//
		// Login stage III: Services setup 
		//
		//####################################################################

		// Skipped


		

		//####################################################################
		//
		// Login stage IV: Final actions 
		//
		//####################################################################
		//******************************************************
		// Send: SNAC(01,1E)  CLI_SETxSTATUS        
		//******************************************************
		$snac_body = 
		$this->make_tlv(0x0006, $this->split_byte(STATUS_DCDISABLED | STATUS_WEBAWARE, 2).$this->split_byte($this->online_status, 2), 0)	// TLV.Type(0x06) - user status / status flags
		;

		$this->fsend($this->add_snac_header($snac_body, 0x0001, 0x001E, 0));


		//******************************************************
		// Overread: SNAC(01,0F)  Requested online info response      
		//******************************************************
		if (!$this->fget_dummy_packet(FLAP_CHANNEL_SNAC_DATA))
		{
			return false;
		}

		//******************************************************
		// Send: SNAC(01,02)  CLI_READY
		// here we have to send supported families
		// see SNAC(01,17)
		//******************************************************
		// Generic service controls
		$snac_body =
		$this->split_byte(0x0001, 2).	// family number 
		$this->split_byte(0x0004, 2).	// family version
		$this->split_byte(54752,  4).	// family dll version
		
		// ICBM (messages) service
		$this->split_byte(0x0004, 2).	// family number 
		$this->split_byte(0x0001, 2).	// family version
		$this->split_byte(54752,  4)	// family dll version
		;

		$this->fsend($this->add_snac_header($snac_body, 0x0001, 0x0002, 0));


		//sleep(1);

		// Disconnect from BOS Server
		//$this->close_connection();

		return true;
	}
	
	//******************************************************
	// Simply returns the error string
	// the error string is filled if a public-class
	// function failed
	//******************************************************
	/**
	* Simply returns the error string.
	* 
	* The error string is filled if a public-class
	* function failed.
	*
	* @return string The formatted error string.
	* @access public
	*/
	function get_error()
	{
		return $this->error;
	}


	/**
	* Sends a plain text message to the chosen uin thru the server
	* 
	* It uses the server to send a message to the chosen contact. If
	* the recipient is offline the server will store the message and sends
	* it when comes online.
	*
	* @param string The recipient uin.
	* @param string The message you want to send.
	* @access public
	*/
	function send_message($target_userid, $message)
	{
		$message_data_tlv =
		$this->split_byte(0x05, 1).					// fragment identifier (array of required capabilities)
		$this->split_byte(0x01, 1).					// fragment version
		$this->split_byte(0x0001, 2).				// Length of rest data
		$this->split_byte(0x01, 1).					// byte array of required capabilities (1 - text)

		$this->split_byte(0x01, 1).					// fragment identifier (array of required capabilities)
		$this->split_byte(0x01, 1).					// fragment version
		$this->split_byte(strlen($message) + 4, 2).	// Length of rest data
		$this->split_byte(0x0003, 2).				// block char set
		$this->split_byte(0x0000, 2).				// block char subset
		$message									// message text string
		;

		$snac_body = 
		$this->split_byte(time() * 1000, 8).				// msg-id cookie  (uptime of the computer)  // wrong not implemented, yet
		$this->split_byte(0x0001, 2).						// message channel
		$this->split_byte(strlen($target_userid), 1).		// screenname string length (reciepient)
		$target_userid.										// screenname string (reciepient)
		$this->make_tlv(0x0002, $message_data_tlv, 0).		// TLV.Type(0x02) - message data
		$this->make_tlv(0x0006, '', 0)						// TLV.Type(0x06) - store message if recipient offline
		;

		$this->fsend($this->add_snac_header($snac_body, 0x0004, 0x0006, 0));
	}
}
?>