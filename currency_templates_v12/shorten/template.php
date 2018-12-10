<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/*
 * Сокращение большой суммы
 * 1000 руб. - 1 тыс. руб.
 * 1000000 руб. - 1 млн. руб.
 * Скрываются нули в сумме, когда число целое
 */
/** @var string $fSum */
/** @var string $strCurrency */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $site_id */
/** @var string $lang */

$arCurFormat = CDemteamCurrencyformat::GetCurrencyFormat($strCurrency, $lang);
$arCurFormat['DECIMALS_ORIG'] = $arCurFormat["DECIMALS"];

// Если цена целое число, то убираем десятичные знаки после запятой
if ((int)$fSum==(float)$fSum) $arCurFormat["DECIMALS"] = 0;

$arCurFormat['DECIMALS_SHORTEN'] = $arCurFormat['DECIMALS_ORIG']>1? ($arCurFormat['DECIMALS_ORIG']-1): 0;
list($fSumShorten, $suffix, $exact) = demteam_currencyformat_shorten($fSum, $arCurFormat['DECIMALS_SHORTEN']);

$num = number_format($fSum, $arCurFormat["DECIMALS"], $arCurFormat["DEC_POINT"], $arCurFormat["THOUSANDS_SEP"]);
if($arCurFormat["THOUSANDS_VARIANT"] == "B")
	$num = str_replace(" ", "&nbsp;", $num);

if ($suffix!==false)
{
	$arCurFormat['FORMAT_STRING_SHORTEN'] = str_replace('#', '#'.(!$exact? '+': '').$suffix, $arCurFormat['FORMAT_STRING']);

	$numShorten = number_format($fSumShorten, $arCurFormat['DECIMALS_SHORTEN'], $arCurFormat["DEC_POINT"], $arCurFormat["THOUSANDS_SEP"]);
	if($arCurFormat["THOUSANDS_VARIANT"] == "B")
		$numShorten = str_replace(" ", "&nbsp;", $numShorten);
		
	echo '<span title="'.str_replace("#", $num, strip_tags($arCurFormat["FORMAT_STRING"])).'">'.str_replace("#", $numShorten, $arCurFormat["FORMAT_STRING_SHORTEN"]).'</span>';
}
else
{
	echo str_replace("#", $num, $arCurFormat["FORMAT_STRING"]);
}