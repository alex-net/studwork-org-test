<?php

class NotesListCest
{
    public function _before(AcceptanceTester $I)
    {
    }

    // tests
    public function tryToTest(AcceptanceTester $I)
    {
    	$I->amOnPage('/note');
    	$I->seeResponceCodeIs(200);
    	//$I->haveHttpHeader('Content-Type','application/json; charset=UTF-8');
    }
}
