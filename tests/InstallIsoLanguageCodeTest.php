<?php

namespace Geonames\Tests;


use Geonames\Models\IsoLanguageCode;

class InstallIsoLanguageCodeTest extends BaseInstallTestCase {

    /**
     * @test
     * @group install
     * @group iso
     */
    public function testIsoLanguageCodeCommand() {
        $this->artisan( 'geonames:iso-language-code', [ '--connection' => $this->DB_CONNECTION ] );
        $isoLanguageCodes = IsoLanguageCode::all();
        $this->assertInstanceOf( \Illuminate\Support\Collection::class, $isoLanguageCodes );
        $this->assertNotEmpty( $isoLanguageCodes );
        $this->assertInstanceOf( IsoLanguageCode::class, $isoLanguageCodes->first() );
    }


    /**
     * @test
     * @group install
     * @group iso
     */
    public function testIsoLanguageCodeCommandFailureWithNonExistentConnection() {
        $this->expectException( \Exception::class );
        $this->artisan( 'geonames:iso-language-code', [ '--connection' => 'i-dont-exist' ] );
    }


}
