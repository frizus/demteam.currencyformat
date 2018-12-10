<?
// Получение параметров для закладки "Дополнительно" в форме редактирования валюты
$arResult['PARAMETERS'] = &CDemteamCurrencyformatParameters::GetParameters('currency_advanced', true);

// Вывод кода
foreach($arResult['PARAMETERS']['ITEMS'] as &$arParameter)
{
	if (array_key_exists('SUBTABS', $arParameter))
		CDemteamCurrencyformatCommon::GenerateSubtabs($arParameter);
	else
		CDemteamCurrencyformatCommon::DrawRow($arParameter);
}