<?
IncludeModuleLangFile(__FILE__);

/* Класс для обработчиков событий модуля */
class CDemteamCurrencyformatEvent
{
	const MODULE_ID = demteam_currencyformat::MODULE_ID;
	const PREFIX = demteam_currencyformat::PREFIX;
	const URL_CURRENCY_EDIT = demteam_currencyformat::URL_CURRENCY_EDIT;

	// Функция для события, которое позволяет изменить или добавить собственные вкладки формы редактирования в административном интерфейсе
	public function OnAdminTabControlBegin(&$form)
	{
		global $APPLICATION;
		if ($APPLICATION->GetCurPage(true)==self::URL_CURRENCY_EDIT)
		{
			$tabs_files = array(
				'additional' => str_replace('\\', '/', dirname(__FILE__)).'/Tabs/additional/template.php', // Страница создания / редактирования валюты
			);
			
			// Добавляем закладку
			$form->tabs[] = array(
				'DIV' => self::PREFIX.'edit1',
				'TAB' => GetMessage("DEMTEAM_CURRENCYFORMAT_DOPOLNITELQNO"),
				'ICON' => 'main_user_edit',
				'TITLE' => GetMessage("DEMTEAM_CURRENCYFORMAT_NASTROYKI_VYVODA_VAL"),
				'CONTENT' => CDemteamCurrencyformatCommon::return_output($tabs_files['additional']),
			);
		}
	}
	
	
	// Функция для события вызываемого в выполняемой части пролога сайта
	public function OnBeforeProlog()
	{
		global $APPLICATION;
		if ($_SERVER['REQUEST_METHOD']=='POST' && $APPLICATION->GetCurPage(true)==self::URL_CURRENCY_EDIT && 
		    $APPLICATION->GetGroupRight('currency')=='W' && check_bitrix_sessid())
		{
			// POST-запрос на странице создания / редактирования валюты
			// У пользователя есть права на запись в модуле "Валюты"
			CDemteamCurrencyformatParameters::SaveParameters('currency_advanced');
		}
	}
	
	
	// Функция для события вызываемого во время удаления сайта
	public function OnSiteDelete($site_id)
	{
		CDemteamCurrencyformatParameters::DeleteSiteParameters($site_id);
		//COption::RemoveOption(self::MODULE_ID, '', $site_id);
	}
	
	
	// Функция для события вызываемого во время удаления языка
	public function OnLanguageDelete($language_id)
	{
		CDemteamCurrencyformatParameters::DeleteLanguageParameters($language_id);
	}
	
}
?>