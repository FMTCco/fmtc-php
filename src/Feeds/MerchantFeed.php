<?php
namespace Fmtc\Feeds;

use Illuminate\Database\Capsule\Manager as DB;
class MerchantFeed extends Feed
{
	protected $url = 'http://services.fmtc.co/v2/getMerchants';

	protected function storeFull($json)
	{	
		$merchants = json_decode($json, true);

		// grab merchants from the database
		$dbMerchants = collect(DB::table('merchants')->get(['nMerchantID', 'dtLastUpdated']))->keyBy('nMerchantID')->toArray();

		// grab an array of columns in the merchants table
		$columns = DB::select('select COLUMN_NAME as `column` from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME = \'fmtc_merchants\'');

		// set the counters for reporting
		$insertCount = 0;
		$updateCount = 0;
		$removeCount = 0;

		// walk through the merchants from a merchant feed
		$jsonMerchantIds = [];
		foreach($merchants as $merchant) {
			// is this merchant in the database?
			if (isset($dbMerchants[$merchant['nMerchantID']])) {
				// has it been updated
				if ($dbMerchants[$merchant['nMerchantID']]->dtLastUpdated != $merchant['dtLastUpdated']) {
					// update it
					DB::table('merchants')
						->where('nMerchantID', $merchant['nMerchantID'])
						->update($this->formatForInsertion($merchant, $columns));
					$updateCount++;
				}
			} else {
				// insert it (this is faster than building an insert queue and bulk inserting)
				DB::table('merchants')->insert($this->formatForInsertion($merchant, $columns));
				$insertCount++;
			}

			// collect an array of ids to aid in the remove	queue
			$jsonMerchantIds[] = $merchant['nMerchantID'];
		}

		// remove old merchants showing up in the database but not in the new merchant feed.
		$removeQueue = array_diff(array_keys($dbMerchants), $jsonMerchantIds);
		$removeCount = count($removeQueue);
		foreach ($removeQueue as $couponId) {
			DB::table('merchants')->where('nMerchantID', $couponId)->delete();
		}

		//---- debugging
		debug($removeCount . ' removed');
		debug($insertCount . ' inserted');
		debug($updateCount . ' updated');
		//----- 
		
		return true;
	}

	protected function storeIncremental($json)
	{
		$merchants = json_decode($json, true);

		// grab merchants from the database
		$dbMerchants = collect(DB::table('merchants')->get(['nMerchantID', 'dtLastUpdated']))->keyBy('nMerchantID')->toArray();

		// grab an array of columns in the merchants table
		$columns = DB::select('select COLUMN_NAME as `column` from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME = \'fmtc_merchants\'');

		// set the counters for reporting
		$insertCount = 0;
		$updateCount = 0;
		$removeCount = 0;

		// walk through the merchants from a merchant feed
		foreach($merchants as $merchant) {
			// is this merchant in the database?
			if (isset($dbMerchants[$merchant['nMerchantID']])) {
				// is it active?
				if ($merchant['cStatus'] == 'active') {
					// has it been updated
					if ($dbMerchants[$merchant['nMerchantID']]->dtLastUpdated != $merchant['dtLastUpdated']) {
						// update it
						DB::table('merchants')
							->where('nMerchantID', $merchant['nMerchantID'])
							->update($this->formatForInsertion($merchant, $columns));
						$updateCount++;
					}
				} else {
					// if it's not active remove it
					DB::table('merchants')->where('nMerchantID', $merchant['nMerchantID'])->delete();
					$removeCount++;
				}
			} else {
				// make sure it's active
				if ($merchant['cStatus'] == 'active') {
					// insert it (this is faster than building an insert queue and bulk inserting)
					DB::table('merchants')->insert($this->formatForInsertion($merchant, $columns));
					$insertCount++;
				}
			}
		}

		//---- debugging
		debug($removeCount . ' removed');
		debug($insertCount . ' inserted');
		debug($updateCount . ' updated');
		//----- 
		
		return true;
	}
}