<?php
namespace Fmtc;

use Fmtc\Feeds\DealFeed;
use Fmtc\Feeds\ApiFeed;
use Fmtc\Feeds\MerchantFeed;
use Fmtc\Feeds\CategoryFeed;
use Fmtc\Feeds\TypeFeed;
use Fmtc\Feeds\NetworkFeed;
use Illuminate\Database\Capsule\Manager as DB;

class Fmtc
{
	protected $config;

	/**
	 * Initialize The Base Fmtc Class.
	 */
	public function __construct($config, $container = null)
	{
		$this->establishConnection($config, $container);
		$this->config = $config;
	}

	public function database()
	{
		return new Database;
	}

	public function api()
	{
		return new ApiFeed($this->config['api_key']);
	}

	public function dealFeed()
	{
		return new DealFeed($this->config['api_key']);
	}

	public function merchantFeed()
	{
		return new MerchantFeed($this->config['api_key']);
	}

	public function categoryFeed()
	{
		return new CategoryFeed($this->config['api_key']);
	}

	public function typeFeed()
	{
		return new TypeFeed($this->config['api_key']);
	}

	public function networkFeed()
	{
		return new NetworkFeed($this->config['api_key']);
	}

	public function deals()
	{
		return new Deals;
	}

	public function merchants()
	{
		return new Merchants;
	}

	public function categories()
	{
		return new Categories;
	}

	public function types()
	{
		return new Types;
	}

	public function networks()
	{
		return new Networks;
	}

	protected function establishConnection($config, $container)
	{
		$db = new DB($container);

		$db->addConnection([
		    'driver'    => 'mysql',
		    'host'      => $config['host'],
		    'database'  => $config['database'],
		    'username'  => $config['username'],
		    'password'  => $config['password'],
		    'charset'   => 'utf8',
		    'collation' => 'utf8_unicode_ci',
		    'prefix'    => 'fmtc_',
		]);

		$db->setAsGlobal();
	}
}
