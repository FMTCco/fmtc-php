<?php
namespace Fmtc; 

use Illuminate\Database\Capsule\Manager as DB;

class Merchants
{
	public function get($id)
	{
		$merchant = DB::table('merchants')
						->where('nMerchantID', $id)
						->first();

		return ($merchant) ? $merchant : false;
	}

	public function all()
	{
		$merchants = DB::table('merchants')
						->orderBy('cName')
						->get();

		return $merchants;
	}

	public function getByMasterMerchant($id)
	{
		$merchants = DB::table('merchants')
						->where('nMasterMerchantID', $id)
						->get();

		return $merchants;
	}

	public function getBySearch($search)
	{
		$merchants = DB::table('merchants')
						->where(function($query) use ($search) {
							$search = '%' . $search . '%';
							$query->where('nMerchantID', 'like', $search)
								  ->orWhere('nMasterMerchantID', 'like', $search)
								  ->orWhere('cName', 'like', $search);
						})
						->get();

		return $merchants;
	}
}
