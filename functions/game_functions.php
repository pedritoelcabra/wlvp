<?php 
function cards(){
    $Cards=array(
            "Reinforcement"     => "WarLight.Shared.GameOrderPlayCardReinforcement",
            "Spy"		=> "WarLight.Shared.GameOrderPlayCardSpy",
            "Surveillance"	=> "WarLight.Shared.GameOrderPlayCardSurveillance",
            "Reconnaissance"    => "WarLight.Shared.GameOrderPlayCardReconnaissance",
            "Airlift"           => "WarLight.Shared.GameOrderPlayCardAirlift",
            "Blockade"          => "WarLight.Shared.GameOrderPlayCardBlockade",
            "Abandon"           => "WarLight.Shared.GameOrderPlayCardAbandon",
            "Delay"		=> "WarLight.Shared.GameOrderPlayCardOrderDelay",
            "Priority"          => "WarLight.Shared.GameOrderPlayCardOrderPriority",
            "Diplomacy"         => "WarLight.Shared.GameOrderPlayCardDiplomacy",
            "Gift"		=> "WarLight.Shared.GameOrderPlayCardGift",
            "Sanctions"         => "WarLight.Shared.GameOrderPlayCardSanctions",
            "Discard"           => "WarLight.Shared.ActiveCardWoreOff"
    );
    return $Cards;
}

