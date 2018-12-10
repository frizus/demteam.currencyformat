<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/*
 * ����� ����� �� ����. ������ ����� ��� ������ RUB � RUR
 * ���������� ���� � �����, ����� ����� �����
 */
/** @var string $fSum */
/** @var string $strCurrency */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $site_id */
/** @var string $lang */

$arCurFormat = CDemteamCurrencyformat::GetCurrencyFormat($strCurrency, $lang);

// ���� ����� ����� �����, �� ������� ���������� ����� ����� �������
if ((int)$fSum==(float)$fSum) $arCurFormat["DECIMALS"] = 0;

if ($strCurrency=='RUB' || $strCurrency=='RUR')
	$arCurFormat['FORMAT_STRING'] = '# '.'<span class="demteam_currency_rouble">c</span>';

$num = number_format($fSum, $arCurFormat["DECIMALS"], $arCurFormat["DEC_POINT"], $arCurFormat["THOUSANDS_SEP"]);
if($arCurFormat["THOUSANDS_VARIANT"] == "B")
	$num = str_replace(" ", "&nbsp;", $num);

echo str_replace("#", $num, $arCurFormat["FORMAT_STRING"]);