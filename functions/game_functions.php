<?php function cards(){
$Cards=array(
	"Reinforcement" => "GameOrderPlayCardReinforcement",
	"Spy"		=> "GameOrderPlayCardSpy",
	"Surveillance"	=> "GameOrderPlayCardSurveillance",
	"Reconnaissance"=> "GameOrderPlayCardReconnaissance",
	"Airlift"	=> "GameOrderPlayCardAirlift",
	"Blockade"	=> "GameOrderPlayCardBlockade",
	"Abandon"	=> "GameOrderPlayCardAbandon",
	"Delay"		=> "GameOrderPlayCardOrderDelay",
	"Priority"	=> "GameOrderPlayCardOrderPriority",
	"Diplomacy"	=> "GameOrderPlayCardDiplomacy",
	"Gift"		=> "GameOrderPlayCardGift",
	"Sanctions"	=> "GameOrderPlayCardSanctions",
	"Discard"	=> "ActiveCardWoreOff"
);
return $Cards;
}

