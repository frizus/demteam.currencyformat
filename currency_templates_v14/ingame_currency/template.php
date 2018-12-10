<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/*
 * Внутриигровая валюта
 */
/** @var string $price */
/** @var string $currency */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $site_id */
/** @var string $lang */

$gold=floor($price/10000);
$silver=floor($price%10000/100);
$copper=$price%100;

echo '<div class="demteam_currency_wrapper">';
if ($gold>0) echo '<span class="demteam_currency_gold">'.$gold.'</span>';
if ($silver>0) echo '<span class="demteam_currency_silver">'.$silver.'</span>';
if ($copper>0) echo '<span class="demteam_currency_copper">'.$copper.'</span>';
echo '</div>';