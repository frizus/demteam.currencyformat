<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/*
 * В сумме дробная часть выводится надстрочно
 * В сумме скрывается 0 в целой части, если сумма меньше 1
 * $.50
 * $3.50
 */
/** @var string $fSum */
/** @var string $strCurrency */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $site_id */
/** @var string $lang */

$arCurFormat = CDemteamCurrencyformat::GetCurrencyFormat($strCurrency, $lang);
	
$num = number_format($fSum, $arCurFormat["DECIMALS"], $arCurFormat["DEC_POINT"], $arCurFormat["THOUSANDS_SEP"]);
if($arCurFormat["THOUSANDS_VARIANT"] == "B")
	$num = str_replace(" ", "&nbsp;", $num);

if ($arCurFormat["DECIMALS"]>0)
{
	// Скрываем ноль в сумме меньшей 1
	if ($fSum<1 && $fSum>0)
		$num = substr($num, 1);
	$num = substr($num, 0, strpos($num,$arCurFormat["DEC_POINT"])).'<sup class="demteam_currency_decimal">'.substr($num, strpos($num,$arCurFormat["DEC_POINT"])+1).'</sup>';
}

echo str_replace("#", $num, $arCurFormat["FORMAT_STRING"]);