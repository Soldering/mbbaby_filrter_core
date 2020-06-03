<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (isset($arParams["TEMPLATE_THEME"]) && !empty($arParams["TEMPLATE_THEME"]))
{
	$arAvailableThemes = array();
	$dir = trim(preg_replace("'[\\\\/]+'", "/", dirname(__FILE__)."/themes/"));
	if (is_dir($dir) && $directory = opendir($dir))
	{
		while (($file = readdir($directory)) !== false)
		{
			if ($file != "." && $file != ".." && is_dir($dir.$file))
				$arAvailableThemes[] = $file;
		}
		closedir($directory);
	}

	if ($arParams["TEMPLATE_THEME"] == "site")
	{
		$solution = COption::GetOptionString("main", "wizard_solution", "", SITE_ID);
		if ($solution == "eshop")
		{
			$theme = COption::GetOptionString("main", "wizard_eshop_adapt_theme_id", "blue", SITE_ID);
			$arParams["TEMPLATE_THEME"] = (in_array($theme, $arAvailableThemes)) ? $theme : "blue";
		}
	}
	else
	{
		$arParams["TEMPLATE_THEME"] = (in_array($arParams["TEMPLATE_THEME"], $arAvailableThemes)) ? $arParams["TEMPLATE_THEME"] : "blue";
	}
}
else
{
	$arParams["TEMPLATE_THEME"] = "blue";
}
$arParams["POPUP_POSITION"] = (isset($arParams["POPUP_POSITION"]) && in_array($arParams["POPUP_POSITION"], array("left", "right"))) ? $arParams["POPUP_POSITION"] : "left";
foreach($arResult["ITEMS"] as $key => $arItem)
{
	if($arItem["CODE"]=="IN_STOCK"){
		sort($arResult["ITEMS"][$key]["VALUES"]);
		if($arResult["ITEMS"][$key]["VALUES"])
			$arResult["ITEMS"][$key]["VALUES"][0]["VALUE"]=$arItem["NAME"];
	}
}
setlocale(LC_COLLATE,'ru_RU.utf8');
foreach ($arResult["ITEMS"] as $code => $arProp) {
    if($arProp["CODE"] != "RAZMER") {
        if (!empty($arProp["VALUES"])) {
            foreach ($arProp["VALUES"] as $i => $val) {
                if (intval($val["VALUE"]) != false) { //якщо це число
                    uasort($arResult["ITEMS"][$code]["VALUES"], function ($a, $b) {
                        $val1 = intval($a["VALUE"]);
                        $val2 = intval($b["VALUE"]);
                        if ($val1 == $val2) {
                            return 0;
                        }
                        return ($val1 < $val2) ? -1 : 1;
                    });
                } else {
                    usort($arResult["ITEMS"][$code]["VALUES"], function ($item1, $item2) {
                        return strcoll($item1["VALUE"], $item2["VALUE"]);
                    });
                }
            }
        }
    }
}
//це сортування по значення для розміру
//foreach ($arResult["ITEMS"] as $i => $arProp) {
//    if ($arProp["CODE"] == "RAZMER") {
//        foreach ($arProp["VALUES"] as $key => $someValue) {
//            if (intval($someValue["VALUE"]) <=0 && $someValue["VALUE"]{0}!=="0") {
//                unset($arResult['ITEMS'][$i]["VALUES"][$key]);
//                $arStringRazmerValue[] = $someValue;
//            }
//        }
//    }
//}
//
//if (!empty($arStringRazmerValue)) {
//    foreach ($arResult["ITEMS"] as $i => $arProp) {
//        if ($arProp["CODE"] == "RAZMER") {
//            $arResult["ITEMS"][$i]["VALUES"] = array_merge($arResult["ITEMS"][$i]["VALUES"], array_reverse($arStringRazmerValue));
//        }
//    }
//}






