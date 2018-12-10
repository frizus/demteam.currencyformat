<?
IncludeModuleLangFile(__FILE__);

/* ����� ��� ������������ ������� ������ */
class CDemteamCurrencyformatEvent
{
	const MODULE_ID = demteam_currencyformat::MODULE_ID;
	const PREFIX = demteam_currencyformat::PREFIX;
	const URL_CURRENCY_EDIT = demteam_currencyformat::URL_CURRENCY_EDIT;

	// ������� ��� �������, ������� ��������� �������� ��� �������� ����������� ������� ����� �������������� � ���������������� ����������
	public function OnAdminTabControlBegin(&$form)
	{
		global $APPLICATION;
		if ($APPLICATION->GetCurPage(true)==self::URL_CURRENCY_EDIT)
		{
			$tabs_files = array(
				'additional' => str_replace('\\', '/', dirname(__FILE__)).'/Tabs/additional/template.php', // �������� �������� / �������������� ������
			);
			
			// ��������� ��������
			$form->tabs[] = array(
				'DIV' => self::PREFIX.'edit1',
				'TAB' => GetMessage("DEMTEAM_CURRENCYFORMAT_DOPOLNITELQNO"),
				'ICON' => 'main_user_edit',
				'TITLE' => GetMessage("DEMTEAM_CURRENCYFORMAT_NASTROYKI_VYVODA_VAL"),
				'CONTENT' => CDemteamCurrencyformatCommon::return_output($tabs_files['additional']),
			);
		}
	}
	
	
	// ������� ��� ������� ����������� � ����������� ����� ������� �����
	public function OnBeforeProlog()
	{
		global $APPLICATION;
		if ($_SERVER['REQUEST_METHOD']=='POST' && $APPLICATION->GetCurPage(true)==self::URL_CURRENCY_EDIT && 
		    $APPLICATION->GetGroupRight('currency')=='W' && check_bitrix_sessid())
		{
			// POST-������ �� �������� �������� / �������������� ������
			// � ������������ ���� ����� �� ������ � ������ "������"
			CDemteamCurrencyformatParameters::SaveParameters('currency_advanced');
		}
	}
	
	
	// ������� ��� ������� ����������� �� ����� �������� �����
	public function OnSiteDelete($site_id)
	{
		CDemteamCurrencyformatParameters::DeleteSiteParameters($site_id);
		//COption::RemoveOption(self::MODULE_ID, '', $site_id);
	}
	
	
	// ������� ��� ������� ����������� �� ����� �������� �����
	public function OnLanguageDelete($language_id)
	{
		CDemteamCurrencyformatParameters::DeleteLanguageParameters($language_id);
	}
	
}
?>