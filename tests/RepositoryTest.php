<?php

namespace Geonames\Tests;


use Geonames\Models\Admin1Code;
use Geonames\Models\Admin2Code;
use Geonames\Models\FeatureClass;
use Geonames\Models\Geoname;
use Geonames\Models\GeoSetting;
use Geonames\Repositories\Admin1CodeRepository;
use Geonames\Repositories\Admin2CodeRepository;
use Geonames\Repositories\AlternateNameRepository;
use Geonames\Repositories\FeatureClassRepository;
use Geonames\Repositories\GeonameRepository;
use Geonames\Repositories\IsoLanguageCodeRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class RepositoryTest extends AbstractGlobalTestCase {


    public function setUp(): void {
        parent::setUp();

        echo "\nRunning setUp() in RepositoryTest...\n";

//        $this->artisan( 'migrate', [ '--database' => $this->DB_CONNECTION, ] );
//        $this->artisan( 'geonames:install', [
//            '--test'       => TRUE,
//            '--connection' => $this->DB_CONNECTION,
//        ] );
        echo "\nDone running setUp() in RepositoryTest.\n";
    }

    protected function getEnvironmentSetUp( $app ) {
        // Setup default database to use sqlite :memory:
        $app[ 'config' ]->set( 'database.default', 'testbench' );
        $app[ 'config' ]->set( 'database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => './tests/files/database.sqlite',
            'prefix'   => '',
        ] );
    }


    /**
     * @test
     * @group repo
     */
    public function theOnlyTest() {
        $this->isoLanguageCode();
        $this->featureClass();
        $this->getStorageDirFromDatabase();
        $this->admin1Code();
        $this->admin2Code();
        $this->alternateName();
        $this->geoname();
    }


    /**
     *
     */
    protected function getStorageDirFromDatabase() {
        $dir = GeoSetting::getStorage();
        $this->assertEquals( $dir, 'geonames' );
    }


    /**
     *
     */
    protected function admin1Code() {
        $repo       = new Admin1CodeRepository();
        $admin1Code = $repo->getByCompositeKey( 'AD', '06' );
        $this->assertInstanceOf( Admin1Code::class, $admin1Code );

        try {
            $repo->getByCompositeKey( 'XX', '00' ); // Does not exist.
        } catch ( \Exception $exception ) {
            $this->assertInstanceOf( ModelNotFoundException::class, $exception );
        }
    }

    /**
     *
     */
    protected function admin2Code() {
        $repo       = new Admin2CodeRepository();
        $admin2Code = $repo->getByCompositeKey( 'AF', '08', 609 );
        $this->assertInstanceOf( Admin2Code::class, $admin2Code );

        try {
            $repo->getByCompositeKey( 'XX', '00', 000 ); // Does not exist.
        } catch ( \Exception $exception ) {
            $this->assertInstanceOf( ModelNotFoundException::class, $exception );
        }
    }


    /**
     *
     */
    protected function alternateName() {
        $repo           = new AlternateNameRepository();
        $alternateNames = $repo->getByGeonameId( 7500737 );
        $this->assertInstanceOf( Collection::class, $alternateNames );
        $this->assertNotEmpty( $alternateNames );


        // Should be an empty Collection
        $alternateNames = $repo->getByGeonameId( 0 );
        $this->assertInstanceOf( Collection::class, $alternateNames );
        $this->assertEmpty( $alternateNames );

        try {
            $repo->getByGeonameId( 0 ); // Does not exist.
        } catch ( \Exception $exception ) {
            $this->assertInstanceOf( ModelNotFoundException::class, $exception );
        }
    }


    /**
     *
     */
    protected function featureClass() {
        $repo         = new FeatureClassRepository();
        $featureClass = $repo->getById( 'R' );
        $this->assertInstanceOf( FeatureClass::class, $featureClass );

        $featureClasses = $repo->all();
        $this->assertNotEmpty( $featureClasses );

        try {
            $repo->getById( 'DOESNOTEXIST' ); // Does not exist.
        } catch ( \Exception $exception ) {
            $this->assertInstanceOf( ModelNotFoundException::class, $exception );
        }
    }


    protected function isoLanguageCode() {
        $repo             = new IsoLanguageCodeRepository();
        $isoLanguageCodes = $repo->all();
        $this->assertInstanceOf( Collection::class, $isoLanguageCodes );
        $this->assertNotEmpty( $isoLanguageCodes );
    }


    /**
     * 7500737
     *
     */
    protected function geoname() {
        $repo = new GeonameRepository();

        $geonames = $repo->getCitiesNotFromCountryStartingWithTerm( 'US', "ka" );
        $this->assertInstanceOf( Collection::class, $geonames );
        $this->assertGreaterThan( 0, $geonames->count() );
        $this->assertInstanceOf( Geoname::class, $geonames->first() );


        $geonames = $repo->getSchoolsFromCountryStartingWithTerm( 'UZ', "uc" );
        $this->assertInstanceOf( Collection::class, $geonames );
        $this->assertGreaterThan( 0, $geonames->count() );
        $this->assertInstanceOf( Geoname::class, $geonames->first() );


        $geonames = $repo->getCitiesFromCountryStartingWithTerm( 'UZ', 'ja' );
        $this->assertInstanceOf( Collection::class, $geonames );
        $this->assertGreaterThan( 0, $geonames->count() );
        $this->assertInstanceOf( Geoname::class, $geonames->first() );

        $geonames = $repo->getPlacesStartingWithTerm( 'Ur' );
        $this->assertInstanceOf( Collection::class, $geonames );
        $this->assertGreaterThan( 0, $geonames->count() );
        $this->assertInstanceOf( Geoname::class, $geonames->first() );

    }




    /**
     * @test1
     */
//    public function getAllLinksOnDownloadPage() {
//        //$this->markTestSkipped('Unable to access the config() helper in this test. Wait until a patch is ready.');
//        $methodName = 'getAllLinksOnDownloadPage';
//        $args       = [];
//        $object     = new UpdateGeonames( new Curl(), new Client() );
//        $reflection = new \ReflectionClass( get_class( $object ) );
//        $method     = $reflection->getMethod( $methodName );
//        $method->setAccessible( true );
//        $links = $method->invokeArgs( $object, $args );
//
//        $this->assertNotEmpty( $links );
//
//        // Not sure what my plan was for testing using this code.
////        foreach ($links as $index => $link) {
////            $matched = (bool)filter_var($link, FILTER_VALIDATE_URL);
////            $this->assertTrue($matched);
////        }
//    }


    /**
     * @test1
     */
//    public function prepareRowsForUpdate() {
////        $this->markTestSkipped('Unable to access the config() helper in this test. Wait until a patch is ready.');
//        $filePath = './tests/files/AD.txt';
//
//        $methodName = 'prepareRowsForUpdate';
//        $args       = [ $filePath ];
//
//        $object = new UpdateGeonames( new Curl(), new Client() );
//
//        $reflection = new \ReflectionClass( get_class( $object ) );
//        $method     = $reflection->getMethod( $methodName );
//        $method->setAccessible( true );
//        $arrayOfStdClassObjects = $method->invokeArgs( $object, $args );
//
//        $this->assertIsArray( $arrayOfStdClassObjects );
//        $this->assertNotEmpty( $arrayOfStdClassObjects );
//    }


}
