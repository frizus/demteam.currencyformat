<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/*
 * Вывод суммы со спец. знаком рубля для валюты RUB и RUR
 */
/** @var string $price */
/** @var string $currency */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $site_id */
/** @var string $lang */

$currency = (string)$currency;
$arCurFormat = CDemteamCurrencyformat::GetCurrencyFormat($currency, $lang);

$intDecimals = $arCurFormat['DECIMALS'];
if ('Y' == $arCurFormat['HIDE_ZERO'])
{
	if (round($price, $arCurFormat["DECIMALS"]) == round($price, 0))
		$intDecimals = 0;
}

if ($currency=='RUB' || $currency=='RUR')
	$arCurFormat['FORMAT_STRING'] = '# '.'<span class="demteam_currency_rouble">c</span>';

$price = number_format($price, $intDecimals, $arCurFormat['DEC_POINT'], $arCurFormat['THOUSANDS_SEP']);
if (CDemteamCurrencyformat::SEP_NBSPACE == $arCurFormat['THOUSANDS_VARIANT'])
	$price = str_replace(' ', '&nbsp;', $price);

echo str_replace('#', $price, $arCurFormat['FORMAT_STRING']);