<?
// ��������� ���������� ��� �������� "�������������" � ����� �������������� ������
$arResult['PARAMETERS'] = &CDemteamCurrencyformatParameters::GetParameters('currency_advanced', true);

// ����� ����
foreach($arResult['PARAMETERS']['ITEMS'] as &$arParameter)
{
	if (array_key_exists('SUBTABS', $arParameter))
		CDemteamCurrencyformatCommon::GenerateSubtabs($arParameter);
	else
		CDemteamCurrencyformatCommon::DrawRow($arParameter);
}