<?php

namespace Geonames\Tests;
namespace Geonames\Tests;

use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;

class WebScrapingTest extends PantherTestCase
{
	public function testMyApp(): void
	{
		// Creating the Chrome client with a specified path to Chrome
		$client = Client::createChromeClient('C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe');
		$client->request('GET', '/home'); // Make sure the built-in server is running or use the full URL if necessary

		// Assertions for page title and body content
		$this->assertPageTitleContains('My Title');
		$this->assertSelectorTextContains('#main', 'My body');

		// Assertions for element visibility and states
		$this->assertSelectorIsEnabled('.search');
		$this->assertSelectorIsDisabled('[type="submit"]');
		$this->assertSelectorIsVisible('.errors');
		$this->assertSelectorIsNotVisible('.loading');

		// Assertions for element attributes
		$this->assertSelectorAttributeContains('.price', 'data-old-price', '42');
		$this->assertSelectorAttributeNotContains('.price', 'data-old-price', '36');

		// Optionally, wait for an element to appear before interacting with it
		$client->waitForSelector('.main-content'); // Waits for the element with class .main-content to appear
	}
}
