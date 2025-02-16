<?php

namespace Geonames\Console;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Symfony\Component\DomCrawler\Crawler;
use Curl\Curl;
use Geonames\Models\GeonamesDelete;
use Geonames\Models\Geoname;
use Geonames\Models\Log;
use Geonames\Models\GeoSetting;

class UpdateGeonames extends Command
{
	use GeonamesConsoleTrait;

	protected $signature = 'geonames:update-geonames
    {--connection= : If you want to specify the name of the database connection you want used.}';

	protected $description = "Download the modifications txt file from geonames.org, then update our database.";

	protected $modificationsTxtFileNamePrefix = 'modifications-';
	protected $modificationsTxtFileName;
	protected $deletesTxtFileName;
	protected $curl;
	protected $urlForDownloadList = 'http://download.geonames.org/export/dump/';
	protected $linksOnDownloadPage = [];
	protected $startTime;
	protected $endTime;
	protected $runTime;
	protected $storageDir;

	/**
	 * UpdateGeonames constructor.
	 *
	 * @param Curl $curl
	 */
	public function __construct(Curl $curl)
	{
		parent::__construct();
		$this->curl = $curl;
	}

	/**
	 * Handle the command.
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function handle()
	{
		ini_set('memory_limit', -1);

		try {
			$this->setDatabaseConnectionName();
			$this->info("The database connection name was set to: " . $this->connectionName);
			$this->comment("Testing database connection...");
			$this->checkDatabase();
			$this->info("Confirmed database connection set up correctly.");
		} catch (\Exception $exception) {
			$this->error($exception->getMessage());
			$this->stopTimer();
			return false;
		}

		GeoSetting::init(
			[GeoSetting::DEFAULT_COUNTRIES_TO_BE_ADDED],
			[GeoSetting::DEFAULT_LANGUAGES],
			GeoSetting::DEFAULT_STORAGE_SUBDIR,
			$this->connectionName
		);
		$this->storageDir = GeoSetting::getStorage($this->connectionName);
		GeoSetting::setStatus(GeoSetting::STATUS_UPDATING, $this->connectionName);
		$this->startTime = (float)microtime(true);
		$this->line("Starting " . $this->signature);

		// Download the file from geonames.org and save it on local storage.
		$localFilePath = $this->saveRemoteModificationsFile();

		// Process the rows to be modified
		$modificationRows = $this->prepareRowsForUpdate($localFilePath);
		$this->comment("\nProcessing the rows to be modified...");
		$bar = $this->output->createProgressBar(count($modificationRows));

		foreach ($modificationRows as $i => $obj) {
			try {
				$geoname = Geoname::firstOrNew(['geonameid' => $obj->geonameid]);
				$geoname->name = $obj->name;
				// ... other properties here ...
				$saveResult = $geoname->save();

				if ($saveResult) {
					Log::modification(
						'',
						"Geoname record [" . $obj->geonameid . "] was updated.",
						"update",
						$this->connectionName
					);
					$bar->advance();
				}

			} catch (\Exception $e) {
				Log::error(
					'',
					"{" . $e->getMessage() . "} Unable to save the geoname record with id: [" . $obj->geonameid . "]",
					'database',
					$this->connectionName
				);
				$bar->advance();
			}
		}
		$bar->finish();
		$this->info("\nDone updating rows!\n");

		// Process deleted rows
		$this->comment("\nStarting to delete rows found in the 'deletes' file.");
		$this->processDeletedRows();
		$this->comment("\nDone deleting rows found in the 'deletes' file.");

		$this->endTime = (float)microtime(true);
		$this->runTime = $this->endTime - $this->startTime;
		Log::info(
			'',
			"Finished updates in " . $localFilePath . " in " . $this->runTime . " seconds.",
			'update',
			$this->connectionName
		);
		$this->line("\nFinished " . $this->signature);
		GeoSetting::setStatus(GeoSetting::STATUS_LIVE, $this->connectionName);

		return true;
	}

	/**
	 * Get all links from the geonames.org download page.
	 *
	 * @return array
	 */
	protected function getAllLinksOnDownloadPage(): array
	{
		$this->curl->get($this->urlForDownloadList);
		if ($this->curl->error) {
			$this->error($this->curl->error_code . ':' . $this->curl->error_message);
			throw new \Exception("Unable to fetch the page.");
		}

		$html = $this->curl->response;
		$crawler = new Crawler($html);
		return $crawler->filter('a')->each(function (Crawler $node) {
			return $node->attr('href');
		});
	}

	/**
	 * Download the modifications file and save it locally.
	 *
	 * @return string The file path
	 * @throws \Exception
	 */
	protected function saveRemoteModificationsFile()
	{
		$this->line("Downloading the modifications file from geonames.org");
		$this->linksOnDownloadPage = $this->getAllLinksOnDownloadPage();
		$this->modificationsTxtFileName = $this->filterModificationsLink($this->linksOnDownloadPage);
		$absoluteUrlToModificationsFile = $this->urlForDownloadList . $this->modificationsTxtFileName;

		// Download the file using curl
		$this->curl->get($absoluteUrlToModificationsFile);
		if ($this->curl->error) {
			$this->error($this->curl->error_code . ':' . $this->curl->error_message);
			Log::error($absoluteUrlToModificationsFile, $this->curl->error_message, $this->curl->error_code, $this->connectionName);
			throw new \Exception("Unable to download the file at '" . $absoluteUrlToModificationsFile . "', " . $this->curl->error_message);
		}

		$data = $this->curl->response;
		$this->info("Downloaded " . $absoluteUrlToModificationsFile);

		$localDirectoryPath = GeoSetting::getAbsoluteLocalStoragePath($this->connectionName);
		$localFilePath = $localDirectoryPath . DIRECTORY_SEPARATOR . $this->modificationsTxtFileName;
		file_put_contents($localFilePath, $data);

		$this->info("Saved modification file to: " . $localFilePath);

		return $localFilePath;
	}

	// Add more helper methods as needed (like prepareRowsForUpdate, filterModificationsLink, etc.)
}
