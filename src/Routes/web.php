<?php

use Illuminate\Support\Facades\Route;


Route::get( '/geonames/search-all', 'Geonames\ControllersGeonamesController@ajaxJquerySearchAll' );

/**
 *
 */
Route::get( '/geonames/{term}', 'Geonames\ControllersGeonamesController@test' );


Route::get( '/geonames/cities/{asciiNameTerm}', 'Geonames\ControllersGeonamesController@citiesUsingLocale' );

Route::get( '/geonames/{countryCode}/cities/{asciiNameTerm}', 'Geonames\ControllersGeonamesController@citiesByCountryCode' );

Route::get( '/geonames/{countryCode}/schools/{asciiNameTerm}', 'Geonames\ControllersGeonamesController@schoolsByCountryCode' );


/**
 * Uncomment these, but you will not be able to cache your routes as referenced below:
 * @url https://laravel.com/docs/7.x/deployment#optimizing-route-loading
 */
//Route::get( '/geonames/examples/vue/element/autocomplete', function () {
//    return view( 'geonames::vue-element-example' );
//} );

//Route::get( '/geonames/examples/jquery/autocomplete', function () {
//    return view( 'geonames::jquery-example' );
//} );
