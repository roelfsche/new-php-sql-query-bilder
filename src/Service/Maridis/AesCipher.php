<?php 
namespace App\Service\Maridis;

/**
 *
 *
 * (c)  rolf.staege@lumturo.net
 *
 * @copyright    Copyright (c) 2018 rolf.staege@lumturo.net
 */

/**
 * AesCipher
 *
 * Encode/Decode text by password using AES-128-CBC algorithm
 *
 * https://gist.github.com/demisang/716250080d77a7f65e66f4e813e5a636
 */
class AesCipher
{
    const CIPHER = 'AES-128-CBC';
    const INIT_VECTOR_LENGTH = 14;

    /**
     * Encoded/Decoded data
     *
     * @var null|string
     */
    protected $data;
    /**
     * Initialization vector value
     *
     * @var string
     */
    protected $initVector;
    /**
     * Error message if operation failed
     *
     * @var null|string
     */
    protected $errorMessage;

    /**
     * AesCipher constructor.
     *
     * @param string      $initVector   Initialization vector value
     * @param string|null $data         Encoded/Decoded data
     * @param string|null $errorMessage Error message if operation failed
     */
    public function __construct($initVector, $data = null, $errorMessage = null)
    {
        $this->initVector = $initVector;
        $this->data = $data;
        $this->errorMessage = $errorMessage;
    }

    /**
     * Decrypt encoded text by AES-128-CBC algorithm
     *
     * @param string $secretKey  16/24/32 -characters secret password
     * @param string $cipherText Encrypted text
     *
     * @return string
     */
    public function decrypt($secretKey, $cipherText)
    {
        // Check secret length
        if (!static::isKeyLengthValid($secretKey))
        {
            throw new \InvalidArgumentException("Secret key's length must be 128, 192 or 256 bits");
        }

        // Trying to get decrypted text
        $decoded = openssl_decrypt(
            $cipherText,
            static::CIPHER,
            $secretKey,
            OPENSSL_RAW_DATA,
            $this->initVector
        );

        if ($decoded === FALSE)
        {
            // Operation failed
            throw new Exception(openssl_error_string());
        }

        return $decoded;
    }

    /**
     * Check that secret password length is valid
     *
     * @param string $secretKey 16/24/32 -characters secret password
     *
     * @return bool
     */
    public static function isKeyLengthValid($secretKey)
    {
        $length = strlen($secretKey);

        return $length == 16 || $length == 24 || $length == 32;
    }

    /**
     * Get encoded/decoded data
     *
     * @return string|null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Get initialization vector value
     *
     * @return string|null
     */
    public function getInitVector()
    {
        return $this->initVector;
    }

    /**
     * Get error message
     *
     * @return string|null
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * Check that operation failed
     *
     * @return bool
     */
    public function hasError()
    {
        return $this->errorMessage !== null;
    }

    /**
     * To string return resulting data
     *
     * @return null|string
     */
    public function __toString()
    {
        return $this->getData();
    }
}
// USAGE

//$secretKey = '26kozQaKwRuNJ24t';
//$text = 'Some text';
//
//$encrypted = AesCipher::encrypt($secretKey, $text);
//$decrypted = AesCipher::decrypt($secretKey, $encrypted);
//
//$encrypted->hasError(); // TRUE if operation failed, FALSE otherwise
//$encrypted->getData(); // Encoded/Decoded result
//$encrypted->getInitVector(); // Get used (random if encode) init vector
//// $decrypted->* has identical methods
