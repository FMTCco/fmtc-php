<?php
namespace Fmtc; 

use Illuminate\Database\Capsule\Manager as DB;

class Networks
{
	public function get($id)
	{
		$merchant = DB::table('networks')
						->where('cSlug', $id)
						->first();

		return ($merchant) ? $merchant : false;
	}

	public function all()
	{
		$networks = DB::table('networks')->get();

		return $networks;
	}

	public function getBySearch($search)
	{
		$networks = DB::table('networks')
						->where(function($query) use ($search) {
							$search = '%' . $search . '%';
							$query->where('cSlug', 'like', $search)
								  ->orWhere('aCountries', 'like', $search)
								  ->orWhere('cName', 'like', $search);
						})
						->get();

		return $networks;
	}
}