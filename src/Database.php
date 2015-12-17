<?php
namespace Fmtc;

class Database
{
	public function migrate()
	{
		$migration = new Migration();
		$migration->migrate();
	}

	public function rollbackMigration()
	{
		$migration = new Migration();
		$migration->rollbackMigration();
	}
}