<?php
/**
 * Cookie.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:MobileDetect!
 * @subpackage	Helpers
 * @since		5.0
 *
 * @date		23.04.14
 */

namespace IPub\MobileDetect\Helpers;

use Nette;

class Cookie extends Nette\Object
{
	protected $name;
	protected $value;
	protected $domain;
	protected $expire;
	protected $path;
	protected $secure;
	protected $httpOnly;

	/**
	 * Constructor.
	 *
	 * @param string                   $name     The name of the cookie
	 * @param string                   $value    The value of the cookie
	 * @param int|string|\DateTime     $expire   The time the cookie expires
	 * @param string                   $path     The path on the server in which the cookie will be available on
	 * @param string                   $domain   The domain that the cookie is available to
	 * @param bool                     $secure   Whether the cookie should only be transmitted over a secure HTTPS connection from the client
	 * @param bool                     $httpOnly Whether the cookie will be made accessible only through the HTTP protocol
	 *
	 * @throws \InvalidArgumentException
	 *
	 * @api
	 */
	public function __construct($name, $value = null, $expire = 0, $path = '/', $domain = null, $secure = false, $httpOnly = true)
	{
		// from PHP source code
		if (preg_match("/[=,; \t\r\n\013\014]/", $name)) {
			throw new Nette\InvalidArgumentException(sprintf('The cookie name "%s" contains invalid characters.', $name));
		}

		if (empty($name)) {
			throw new Nette\InvalidArgumentException('The cookie name cannot be empty.');
		}

		// convert expiration time to a Unix timestamp
		if ($expire instanceof \DateTime) {
			$expire = $expire->format('U');

		} elseif (!is_numeric($expire)) {
			$expire = strtotime($expire);

			if (false === $expire || -1 === $expire) {
				throw new Nette\InvalidArgumentException('The cookie expiration time is not valid.');
			}
		}

		$this->name		= $name;
		$this->value	= $value;
		$this->domain	= $domain;
		$this->expire	= $expire;
		$this->path		= empty($path) ? '/' : $path;
		$this->secure	= (bool) $secure;
		$this->httpOnly	= (bool) $httpOnly;
	}

	/**
	 * Returns the cookie as a string.
	 *
	 * @return string The cookie
	 */
	public function __toString()
	{
		$str = urlencode($this->getName()).'=';

		if ('' === (string) $this->getValue()) {
			$str .= 'deleted; expires='.gmdate("D, d-M-Y H:i:s T", time() - 31536001);

		} else {
			$str .= urlencode($this->getValue());

			if ($this->getExpiresTime() !== 0) {
				$str .= '; expires='.gmdate("D, d-M-Y H:i:s T", $this->getExpiresTime());
			}
		}

		if ($this->path) {
			$str .= '; path='.$this->path;
		}

		if ($this->getDomain()) {
			$str .= '; domain='.$this->getDomain();
		}

		if (true === $this->isSecure()) {
			$str .= '; secure';
		}

		if (true === $this->isHttpOnly()) {
			$str .= '; httponly';
		}

		return $str;
	}

	/**
	 * Gets the name of the cookie.
	 *
	 * @return string
	 *
	 * @api
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Gets the value of the cookie.
	 *
	 * @return string
	 *
	 * @api
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Gets the domain that the cookie is available to.
	 *
	 * @return string
	 *
	 * @api
	 */
	public function getDomain()
	{
		return $this->domain;
	}

	/**
	 * Gets the time the cookie expires.
	 *
	 * @return int
	 *
	 * @api
	 */
	public function getExpiresTime()
	{
		return $this->expire;
	}

	/**
	 * Gets the path on the server in which the cookie will be available on.
	 *
	 * @return string
	 *
	 * @api
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * Checks whether the cookie should only be transmitted over a secure HTTPS connection from the client.
	 *
	 * @return bool
	 *
	 * @api
	 */
	public function isSecure()
	{
		return $this->secure;
	}

	/**
	 * Checks whether the cookie will be made accessible only through the HTTP protocol.
	 *
	 * @return bool
	 *
	 * @api
	 */
	public function isHttpOnly()
	{
		return $this->httpOnly;
	}

	/**
	 * Whether this cookie is about to be cleared
	 *
	 * @return bool
	 *
	 * @api
	 */
	public function isCleared()
	{
		return $this->expire < time();
	}
}