<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/*
 * Сокращение большой суммы
 * 1000 руб. - 1 тыс. руб.
 * 1000000 руб. - 1 млн. руб.
 */
/** @var string $price */
/** @var string $currency */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $site_id */
/** @var string $lang */

$currency = (string)$currency;
$arCurFormat = CDemteamCurrencyformat::GetCurrencyFormat($currency, $lang);
$arCurFormat['DECIMALS_ORIG'] = $arCurFormat["DECIMALS"];

$intDecimals = $arCurFormat['DECIMALS'];
if ('Y' == $arCurFormat['HIDE_ZERO'])
{
	if (round($price, $arCurFormat["DECIMALS"]) == round($price, 0))
		$intDecimals = 0;
}

$arCurFormat['DECIMALS_SHORTEN'] = $arCurFormat['DECIMALS_ORIG']>1? ($arCurFormat['DECIMALS_ORIG']-1): 0;
list($priceShorten, $suffix, $exact) = demteam_currencyformat_shorten($price, $arCurFormat['DECIMALS_SHORTEN']);

$price = number_format($price, $intDecimals, $arCurFormat['DEC_POINT'], $arCurFormat['THOUSANDS_SEP']);
if (CDemteamCurrencyformat::SEP_NBSPACE == $arCurFormat['THOUSANDS_VARIANT'])
	$price = str_replace(' ', '&nbsp;', $price);

if ($suffix!==false)
{
	$arCurFormat['FORMAT_STRING_SHORTEN'] = str_replace('#', '#'.(!$exact? '+': '').$suffix, $arCurFormat['FORMAT_STRING']);

	$priceShorten = number_format($priceShorten, $arCurFormat['DECIMALS_SHORTEN'], $arCurFormat["DEC_POINT"], $arCurFormat["THOUSANDS_SEP"]);
	if($arCurFormat["THOUSANDS_VARIANT"] == CDemteamCurrencyformat::SEP_NBSPACE)
		$priceShorten = str_replace(" ", "&nbsp;", $priceShorten);
		
	echo '<span title="'.str_replace("#", $price, strip_tags($arCurFormat["FORMAT_STRING"])).'">'.str_replace("#", $priceShorten, $arCurFormat["FORMAT_STRING_SHORTEN"]).'</span>';
}
else
{
	echo str_replace('#', $price, $arCurFormat['FORMAT_STRING']);
}