<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/*
 * В сумме дробная часть выводится надстрочно
 * В сумме скрывается 0 в целой части, если сумма меньше 1
 * $.50
 * $3.50
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
$price = number_format($price, $intDecimals, $arCurFormat['DEC_POINT'], $arCurFormat['THOUSANDS_SEP']);
if (CDemteamCurrencyformat::SEP_NBSPACE == $arCurFormat['THOUSANDS_VARIANT'])
	$price = str_replace(' ', '&nbsp;', $price);

if ($intDecimals>0)
{
	// Скрываем ноль в сумме меньшей 1
	if ($price<1 && $price>0)
		$price = substr($price, 1);
	$price = substr($price, 0, strpos($price,$arCurFormat["DEC_POINT"])).'<sup class="demteam_currency_decimal">'.substr($price, strpos($price,$arCurFormat["DEC_POINT"])+1).'</sup>';
}

echo str_replace('#', $price, $arCurFormat['FORMAT_STRING']);