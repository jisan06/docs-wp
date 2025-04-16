<?php
/**
 * FolioKit
 *
 * @copyright   Copyright (C) 2015 Johan Janssens and Timble CVBA. (http://www.timble.net)
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        https://github.com/easydoclabs/foliokit for the canonical source repository
 */
namespace EasyDocLabs\Component\Base;

use EasyDocLabs\Library;
use EasyDocLabs\WP;

/**
 * Optionable Controller Behavior
 *
 * @author  Johan Janssens <https://github.com/johanjanssens>
 * @package EasyDocLabs\Component\Base
 */
class ControllerBehaviorOptionable extends Library\ControllerBehaviorAbstract
{
    /**
     * The options
     *
     * @var Library\ObjectConfigInterface
     */
    protected $_options;

	/**
	 * The blocks to load options for
	 *
	 * @var array
	 */
	protected $_blocks;

	/**
	 * A list of default block options with their corresponding values
	 *
	 * These options are normally the ones that are not included in block and thus
	 * not configurable yet used currently on layouts
	 *
	 * @var array
	 */
	protected $_block_defaults;

	/**
	 * A list of default endpoint options with their corresponding values
	 *
	 * These options will get merged on top of default block options and serve as a
	 * way to override those values while dispatching the application through an
	 * endpoint
	 *
	 * @var array
	 */
	protected $_endpoint_defaults;

	/**
	 * A list of referrable options as defined in block attributes
	 *
	 * @var array
	 */
	protected $_referrable = [];

	/**
	 * A list of attributes aliases as defined in block attributes
	 *
	 * @var array
	 */
	protected $_aliases = [];

	/**
	 * A list of attribute filters as defined in block attributes
	 *
	 * @var array
	 */
	protected $_filters = [];

	/**
	 * Cipher method used for encrypting query options
	 *
	 * @var string
	 */
	protected $_cipher;

	public function __construct(Library\ObjectConfig $config)
	{
		parent::__construct($config);

		$this->_block_defaults    = $config->block_defaults;
		$this->_endpoint_defaults = $config->endpoint_defaults;
		$this->_cipher            = $config->cipher;

		$this->setOptionable($config);
	}

    protected function _initialize(Library\ObjectConfig $config)
    {
		$config->append([
			'priority'          => Library\BehaviorAbstract::PRIORITY_HIGH,
			'blocks'            => [],
			'options'           => [],
			'block_defaults'    => [],
			'endpoint_defaults' => [],
			'cipher' 			=> 'aes-128-ctr'
		]);

        parent::_initialize($config);
    }

	public function setOptionable($config = [])
	{
		$config = new Library\ObjectConfig($config);

		$this->_initialize($config);

		$this->_setBlocks(Library\ObjectConfig::unbox($config->blocks));

		$this->setOptions($config->options);

		return $this;
	}

	protected function _setBlocks($blocks)
	{
		$blocks = is_array($blocks) ? $blocks : [$blocks];

		$this->_blocks = [];

		$referrable = [];
		$filters    = [];
		$aliases    = [];

		foreach ($blocks as $block)
		{
			if (!$block instanceof BlockInterface) {
				$block = $this->getObject($block);
			}

			foreach ($block->getAttributes() as $name => $data)
			{
				if (!$this->_isEndPointRequest())
				{
					if (isset($data['referrable'])) $referrable[] = $name;

					if (isset($data['alias'])) $aliases[$data['alias']] = $name;

					if (isset($data['filter'])) $filters[$name] = $data['filter'];
				}
			}

			$this->_blocks[] = $block;
		}

		$this->_referrable = $referrable;
		$this->_filters    = $filters;
		$this->_aliases    = $aliases;

		return $this;
	}

	public function setOption($key, $value)
	{
		if (isset($this->_filters[$key])) {
			$value = $this->_filters[$key]($value); // Apply filter
		}

        $result = $this->getOptions()->set($key, $value);

		if ($result && isset($this->_aliases[$key])) {
			$this->setOption($this->_aliases[$key], $value);  // Set alias
		}

		return $result;
    }

    public function getOption($key, $default = null)
    {
        return $this->getOptions()->get($key, $default);

    }

    public function setOptions($options)
    {
        $this->_options = new Library\ObjectConfig($this->_getDefaultOptions() ?? []);

		foreach ($options as $key => $value) {
			$this->setOption($key, $value);
		}

		$query = $this->getRequest()->getQuery();

        if ($this->_isEndPointRequest() && $query->options)
		{
			$query_options = $this->_decryptQueryOptions($query->options);

			// Override options from query for endpoint requests ONLY

			foreach ($query_options as $key => $value) {
				$this->setOption($key, $value);
			}
        }
    }

	public function getOptions()
    {
        return $this->_options;
    }

	protected function _isEndPointRequest()
	{
		return $this->getMixer()->getRequest()->getQuery()->has('endpoint');
	}

	public function getQueryOptions()
	{
		$options = [];

		foreach ($this->_referrable as $option) {
			if (isset($this->_options[$option])) $options[$option] = $this->_options[$option];
		}

		return !empty($options) ? urlencode($this->_encryptQueryOptions($options)) : false;
	}

    protected function _decryptQueryOptions($options)
    {
		list($crypted_token, $iv) = explode('::', $options);

		$decrypted_token = openssl_decrypt($crypted_token, $this->_cipher, $this->_getEncryptionKey(), 0, hex2bin($iv));

		$token = $this->getObject('lib:http.token')->fromString($decrypted_token);

		if (!$token->verify($this->_getPrivateKey())) throw new \RuntimeException('Query options are not valid');

        return $token->getClaim('options');
    }

    protected function _encryptQueryOptions($options)
    {
		// Generate the token containing the options

		$token = $this->getObject('lib:http.token')->setClaim('options', $options)->sign($this->_getPrivateKey());

		// Encrypt the token

		$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->_cipher));

		return openssl_encrypt($token, $this->_cipher, $this->_getEncryptionKey(), 0, $iv) . "::" . bin2hex($iv);
    }

	protected function _getPrivateKey()
	{
		return WP::wp_salt('AUTH_SALT');
	}

	protected function _getEncryptionKey()
	{
		return openssl_digest($this->_getPrivateKey(), 'SHA256', true);
	}

    protected function _getDefaultOptions()
    {
		$options = $this->_block_defaults;

		if ($this->_isEndPointRequest()) {
			$options->merge($this->_endpoint_defaults);
		}

		foreach ($this->_blocks as $block)
		{
			foreach ($block->getAttributes() as $name => $data)
			{
				if (isset($data['default']) && !array_key_exists($name, Library\ObjectConfig::unbox($options))) {
					$options[$name] = $data['default'];
				}

				if (isset($options[$name]) && isset($this->_filters[$name])) {
					$options[$name] = $this->_filters[$name]($data['default']); // Apply filter
				}

				if (isset($options[$name]) && isset($this->_aliases[$name])) {
					$options[$this->_aliases[$name]] = $options[$name]; // Set alias
				}
			}
		}

        return $options;
    }

    protected function _beforeRender(Library\ControllerContext $context)
    {
		$this->getView()->addBehavior('optionable',['options' => $this->getOptions(), 'query_options' => $this->getQueryOptions()]);
    }
}
