<?php
namespace Fmtc\Feeds;

use Illuminate\Database\Capsule\Manager as DB;

class DealFeed extends Feed
{
	protected $url = 'http://services.fmtc.co/v2/getDeals';

	protected function storeFull($json)
	{	
		$deals = json_decode($json, true);

		// grab deals from the database
		$dbDealsRows = DB::table('deals')->get(['nCouponID', 'cLastUpdated']);
		$dbDeals = [];
		foreach ($dbDealsRows as $row) {
			$dbDeals[$row->nCouponID] = $row;
		}

		// grab an array of columns in the deals table
		$columns = DB::select('select COLUMN_NAME as `column` from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME = \'fmtc_deals\'');

		// set the counters for reporting
		$insertCount = 0;
		$updateCount = 0;
		$removeCount = 0;

		// walk through the deals from a deal feed
		$jsonDealIds = [];
		foreach($deals as $deal) {
			// is this deal in the database?
			if (isset($dbDeals[$deal['nCouponID']])) {
				// has it been updated
				if ($dbDeals[$deal['nCouponID']]->cLastUpdated != $deal['cLastUpdated']) {
					// update it
					$this->updateDeal($deal, $columns);
					$updateCount++;
				}
			} else {
				// insert it (this is faster than building an insert queue and bulk inserting)
				$this->insertDeal($deal, $columns);	
				$insertCount++;
			}

			// collect an array of ids to aid in the remove	queue
			$jsonDealIds[] = $deal['nCouponID'];
		}

		// remove old deals showing up in the database but not in the new deal feed.
		$removeQueue = array_diff(array_keys($dbDeals), $jsonDealIds);
		$removeCount = count($removeQueue);

		$this->removeDeals($removeQueue);

		//---- debugging
		debug($removeCount . ' removed');
		debug($insertCount . ' inserted');
		debug($updateCount . ' updated');
		//----- 
		
		return true;
	}

	protected function storeIncremental($json)
	{
		$deals = json_decode($json, true);

		// grab deals from the database
		$dbDealsRows = DB::table('deals')->get(['nCouponID', 'cLastUpdated']);
		foreach ($dbDealsRows as $row) {
			$dbDeals[$row->nCouponID] = $row;
		}

		// grab an array of columns in the deals table
		$columns = DB::select('select COLUMN_NAME as `column` from INFORMATION_SCHEMA.COLUMNS where TABLE_NAME = \'fmtc_deals\'');

		// set the counters for reporting
		$insertCount = 0;
		$updateCount = 0;
		$removeCount = 0;

		// walk through the deals from a deal feed
		foreach($deals as $deal) {
			// is this deal in the database?
			if (isset($dbDeals[$deal['nCouponID']])) {
				// is it active?
				if ($deal['cStatus'] == 'active') {
					// has it been updated
					if ($dbDeals[$deal['nCouponID']]->cLastUpdated != $deal['cLastUpdated']) {
						// update it
						$this->updateDeal($deal, $columns);
						$updateCount++;
					}
				} else {
					// if it's not active remove it
					$this->removeDeal($deal['nCouponID']);
					$removeCount++;
				}
			} else {
				// make sure it's active
				if ($deal['cStatus'] == 'active') {
					// insert it (this is faster than building an insert queue and bulk inserting)
					$this->insertDeal($deal, $columns);
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

	protected function insertDeal($deal, $columns)
	{
		foreach ($deal['aCategories'] as $category) {
			DB::table('deals_categories')->insert(['nCouponID' => $deal['nCouponID'],'cCategorySlug' => $category]);
		}

		foreach ($deal['aTypes'] as $type) {
			DB::table('deals_types')->insert(['nCouponID' => $deal['nCouponID'],'cTypeSlug' => $type]);
		}

		DB::table('deals')->insert($this->formatForInsertion($deal, $columns));
	}

	protected function updateDeal($deal, $columns)
	{
		$nCouponID = $deal['nCouponID'];

		DB::table('deals_categories')->where('nCouponID', $nCouponID)->delete();
		foreach ($deal['aCategories'] as $category) {
			DB::table('deals_categories')->insert(['nCouponID' => $deal['nCouponID'],'cCategorySlug' => $category]);
		}

		DB::table('deals_types')->where('nCouponID', $nCouponID)->delete();
		foreach ($deal['aTypes'] as $type) {
			DB::table('deals_types')->insert(['nCouponID' => $deal['nCouponID'],'cTypeSlug' => $type]);
		}

		DB::table('deals')
			->where('nCouponID', $deal['nCouponID'])
			->update($this->formatForInsertion($deal, $columns));
	}

	public function removeDeals($removeQueue)
	{
		foreach ($removeQueue as $couponId) {
			$this->removeDeal($couponId);
		}
	}

	public function removeDeal($couponId)
	{
		DB::table('deals_categories')->where('nCouponID', $couponId)->delete();
		DB::table('deals_types')->where('nCouponID', $couponId)->delete();
		DB::table('deals')->where('nCouponID', $couponId)->delete();
	}
}