<?php
//Get API key
$q_api = 'SELECT api_key FROM login ORDER BY id ASC LIMIT 1';
$r_api = mysqli_query($mysqli, $q_api);
if ($r_api) while($row = mysqli_fetch_array($r_api)) define('API_KEY', $row['api_key']);

//Encryption method
$encryptionMethod = "AES-256-CBC";

//Encrypt a value
function encrypt_val($val)
{
	global $encryptionMethod;
	if(version_compare(PHP_VERSION, '5.3.0') >= 0 && function_exists('openssl_encrypt')) //openssl_encrypt requires at least 5.3.0
	{
		$encrypted = version_compare(PHP_VERSION, '5.3.3') >= 0 ? openssl_encrypt($val, $encryptionMethod, API_KEY, 0, '3j9hwG7uj8uvpRAT') : openssl_encrypt($val, $encryptionMethod, API_KEY, 0);
		if(!$encrypted) 
		{
			$error = 'Unable to encrypt string with openssl_encrypt()';
			error_log("[$error]".': in '.__FILE__.' on line '.__LINE__);
			echo $error;
			exit;
		}
		
		$encrypted = str_replace('/', '892', $encrypted);
		$encrypted = str_replace('+', '763', $encrypted);
		$encrypted = str_replace('=', '', $encrypted);
		
		return $encrypted;
	}
	else
	{
		$error = 'Unable to use openssl_encrypt() because PHP version is lower than 5.3 and openssl_encrypt() does not exist';
		error_log("[$error]".': in '.__FILE__.' on line '.__LINE__);
		echo $error;
		exit;
	}
}

//Decrypt a string
function decrypt_string($val)
{
	global $encryptionMethod;
	
	if(version_compare(PHP_VERSION, '5.3.0') >= 0 && function_exists('openssl_decrypt')) //openssl_decrypt requires at least 5.3.0
	{
		$decrypted = str_replace('892', '/', $val);
		$decrypted = str_replace('763', '+', $decrypted);
		
		$decrypted = version_compare(PHP_VERSION, '5.3.3') >= 0 ? openssl_decrypt($decrypted, $encryptionMethod, API_KEY, 0, '3j9hwG7uj8uvpRAT') : openssl_decrypt($decrypted, $encryptionMethod, API_KEY, 0);
		if(!$decrypted) 
		{
			if(!empty($decrypted))
			{
				$error = 'Unable to decrypt string with openssl_decrypt()';
				error_log("[$error]".': in '.__FILE__.' on line '.__LINE__);
				echo $error;
				exit;
			}
		}
		
		return $decrypted;
	}
	else
	{
		$error = 'Unable to use openssl_decrypt() because PHP version is lower than 5.3 and openssl_decrypt() does not exist';
		error_log("[$error]".': in '.__FILE__.' on line '.__LINE__);
		echo $error;
		exit;
	}
}

//Decrypt an integer
function decrypt_int($in)
{
	global $encryptionMethod;

	if(version_compare(PHP_VERSION, '5.3.0') >= 0 && function_exists('openssl_decrypt')) //openssl_decrypt requires at least 5.3.0
	{
		$decrypted = str_replace('892', '/', $in);
		$decrypted = str_replace('763', '+', $decrypted);

		$decrypted = version_compare(PHP_VERSION, '5.3.3') >= 0 ? openssl_decrypt($decrypted, $encryptionMethod, API_KEY, 0, '3j9hwG7uj8uvpRAT') : openssl_decrypt($decrypted, $encryptionMethod, API_KEY, 0);
		if($decrypted === false || !is_numeric($decrypted))
		{
			if(!empty($decrypted))
			{
				$error = 'Unable to decrypt string with openssl_decrypt()';
				error_log("[$error]".': in '.__FILE__.' on line '.__LINE__);
				echo $error;
				exit;
			}
		}

		return (int) $decrypted;
	}
	else
	{
		$error = 'Unable to use openssl_decrypt() because PHP version is lower than 5.3 and openssl_decrypt() does not exist';
		error_log("[$error]".': in '.__FILE__.' on line '.__LINE__);
		echo $error;
		exit;
	}
}
?>