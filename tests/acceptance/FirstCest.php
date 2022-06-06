<?php

use PHPUnit\Framework\Assert;

class FirstCest
{
	// tests
	public function tryToTest(AcceptanceTester $I)
	{
		$I->amOnPage('/notes');
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseMatchesJsonType([
			"notes" => 'array',
			"count" => 'integer',
			'pageCount' => 'integer',
			'currentPage' => 'integer',
		]);

		$content = json_decode($I->grabPageSource(), 1);
		Assert::assertEquals(5, count($content['notes']));
		Assert::assertEquals(1, $content['currentPage']);
		$records = $content['count'];
		$I->amGoingTo("Переходим на поседнюю страницу списка");
		// перешли на последнюю страницу ... 
		$I->amOnPage('/notes?p=' . $content['pageCount']);
		$content = json_decode($I->grabPageSource(), 1);
		Assert::assertEquals($content['pageCount'], $content['currentPage']);
		Assert::assertEquals($records, $content['count']);
		$I->amGoingTo("Добавим запись от юзера 100");
		// просто переход на страницу .. 
		$res = $I->sendGet('/new');
		$I->seeResponseCodeIs(401);
		
		$note = [
			'capt' => 'test capt codecept',
			'body' => 'test body codecept',
			'dpubl' => time(),
		];
		$I->sendPost('/new', $note);
		$I->seeResponseCodeIs(401);
		$I->amBearerAuthenticated('100-token');
		$I->sendPost('/new', $note);
		$I->seeResponseCodeIs(200);
		$I->seeResponseMatchesJsonType([
			'capt' => 'string',
			'user' => 'string',
			'dpubl' => 'string'
		]);
		$I->deleteHeader('Authorization');
		$I->sendGet("");
		$I->seeResponseCodeIs(200);
		$content = json_decode($I->grabPageSource(), 1);

		Assert::assertEquals($content['count'], $records + 1);
	}
}
