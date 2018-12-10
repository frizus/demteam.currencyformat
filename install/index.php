<?
IncludeModuleLangFile(__FILE__);

if (class_exists('demteam_currencyformat')) return;

class demteam_currencyformat extends CModule
{
	const MODULE_ID = 'demteam.currencyformat';
	const PREFIX = 'demteam_';
	const URL_CURRENCY_EDIT = '/bitrix/admin/currency_edit.php';
	const BASE_CLASS = 'CDemteamCurrencyformat';
	const EVENT_CLASS = 'CDemteamCurrencyformatEvent';
	var $MODULE_ID = 'demteam.currencyformat';
	public $bNotOutput;
	public $MODULE_VERSION;
	public $MODULE_VERSION_DATE;
	public $MODULE_DESCRIPTION;
	public $MODULE_NAME;
	public $MODULE_GROUP_RIGHTS = 'N';
	public $NEED_MAIN_VERSION = '';
	public $NEED_MODULES = array('currency');

	public function __construct()
	{
		$arModuleVersion = array();
		
		include(str_replace('\\', '/', dirname(__FILE__)).'/version.php');
		
		if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion['VERSION'];
			$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		}
		
		$this->PARTNER_NAME = "Demteam";
		$this->PARTNER_URI = "http://www.demteam.ru";
		
		$this->MODULE_NAME = GetMessage("demteam.currencyformat_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("demteam.currencyformat_MODULE_DESC");
	}

	public function DoInstall()
	{
		global $APPLICATION, $adminPage, $USER, $adminMenu, $adminChain;

		if ($GLOBALS['APPLICATION']->GetGroupRight('main') < 'W')
			return;

		if (is_array($this->NEED_MODULES) && !empty($this->NEED_MODULES))
			foreach ($this->NEED_MODULES as $module)
				if (!IsModuleInstalled($module))
					$this->ShowForm('ERROR', GetMessage('demteam.currencyformat_NEED_MODULES', array('#MODULE#' => $module)));
		
		if (strlen($this->NEED_MAIN_VERSION)<=0 || version_compare(SM_VERSION, $this->NEED_MAIN_VERSION)>=0)
		{
			if (version_compare(SM_VERSION, '9.5.10')>=0)
			{
				RegisterModuleDependences('main', 'OnAdminTabControlBegin', $this->MODULE_ID, self::EVENT_CLASS, 'OnAdminTabControlBegin');
				RegisterModuleDependences('main', 'OnBeforeProlog', $this->MODULE_ID, self::EVENT_CLASS, 'OnBeforeProlog');
			}
			RegisterModuleDependences('main', 'OnSiteDelete', $this->MODULE_ID, self::EVENT_CLASS, 'OnSiteDelete');
			RegisterModuleDependences('main', 'OnLanguageDelete', $this->MODULE_ID, self::EVENT_CLASS, 'OnLanguageDelete');
			if (version_compare(SM_VERSION, '8.0.2')>=0)
			{
				RegisterModuleDependences('currency', 'CurrencyFormat', $this->MODULE_ID, self::BASE_CLASS, 'CurrencyFormat');
			}
			RegisterModule($this->MODULE_ID);
			
			$details = array();
			if (version_compare(SM_VERSION, '9.5.10')>=0)
			{
				// Описание решения доступно в закладке
				$details[] = GetMessage('demteam.currencyformat_LOOK_URL', array('#LINK#'=>self::URL_CURRENCY_EDIT.'?lang='.LANGUAGE_ID));
			}
			else
			{
				// Инструкция по работе доступна странице решения
				$details[] = GetMessage('demteam.currencyformat_SOLUTION_URL', array('#MODULE_ID#'=>self::MODULE_ID));
				if (version_compare(SM_VERSION, '8.0.2')<0)
				{
					// Для получения форматированной валюты используйте функцию
					$details[] = GetMessage('demteam.currencyformat_USE_FUNCTION', array('#BASE_CLASS#'=>self::BASE_CLASS,'#CURRENCY_FORMAT_ARGS_BY_VERSION#'=>self::CurrencyFormatParams()));
				}
			}
			
			$this->ShowForm('OK', GetMessage('MOD_INST_OK'), implode('<br>', $details));
		}
		else
		{
			$this->ShowForm('ERROR', GetMessage('demteam.currencyformat_NEED_RIGHT_VER', array('#NEED#' => $this->NEED_MAIN_VERSION)));
		}	
	}

	public function DoUninstall()
	{
		global $APPLICATION, $adminPage, $USER, $adminMenu, $adminChain;

		if ($GLOBALS['APPLICATION']->GetGroupRight('main') < 'W')
			return;
		
		if (version_compare(SM_VERSION, '9.5.10')>=0)
		{
			UnRegisterModuleDependences('main', 'OnAdminTabControlBegin', $this->MODULE_ID, self::EVENT_CLASS, 'OnAdminTabControlBegin');
			UnRegisterModuleDependences('main', 'OnBeforeProlog', $this->MODULE_ID, self::EVENT_CLASS, 'OnBeforeProlog');
		}
		UnRegisterModuleDependences('main', 'OnSiteDelete', $this->MODULE_ID, self::EVENT_CLASS, 'OnSiteDelete');
		UnRegisterModuleDependences('main', 'OnLanguageDelete', $this->MODULE_ID, self::EVENT_CLASS, 'OnLanguageDelete');
		if (version_compare(SM_VERSION, '8.0.2')>=0)
		{
			UnRegisterModuleDependences('currency', 'CurrencyFormat', $this->MODULE_ID, self::BASE_CLASS, 'CurrencyFormat');
		}
		UnRegisterModule($this->MODULE_ID);
		CDemteamCurrencyformatParameters::DeleteAllParameters();
		$this->ShowForm('OK', GetMessage('MOD_UNINST_OK'));
	}
	
	public function CurrencyFormatParams()
	{
		$CurrencyVersion = self::CurrencyVersion();
		if ($CurrencyVersion==14) return '$price, $currency';
		else return '$fSum, $strCurrency';
	}
	
	// Функция для определения версии модуля валют
	public function CurrencyVersion()
	{
		static $CurrencyVersion;
		if (isset($CurrencyVersion)) return $CurrencyVersion;
		
		if ($info = @CModule::CreateModuleObject('currency'))
			if (version_compare($info->MODULE_VERSION, '14.0.0')>=0) $CurrencyVersion = 14;
			else $CurrencyVersion = 12;
		else $CurrencyVersion = 12;
		
		return $CurrencyVersion;
	}
	
	private function ShowForm($type, $message, $details, $buttonName='') {
		if ($this->bNotOutput)
			return;

		global $APPLICATION, $adminPage, $USER, $adminMenu, $adminChain;
		
		$keys = array_keys($GLOBALS);
		for($i=0; $i<count($keys); $i++)
			if($keys[$i]!='i' && $keys[$i]!='GLOBALS' && $keys[$i]!='strTitle' && $keys[$i]!='filepath')
				global ${$keys[$i]};

		$PathInstall = str_replace('\\', '/', dirname(__FILE__));
		IncludeModuleLangFile($PathInstall.'/install.php');

		$GLOBALS['APPLICATION']->SetTitle($this->MODULE_NAME);
		include($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/prolog_admin_after.php');
		echo CAdminMessage::ShowMessage(array('MESSAGE' => $message, 'HTML'=>true, 'DETAILS' => $details, 'TYPE' => $type));
		?>
		<form action="<?= $APPLICATION->GetCurPage()?>" method="get">
		<p>
			<input type="hidden" name="lang" value="<?= LANGUAGE_ID; ?>" />
			<input type="submit" value="<?= strlen($buttonName) ? $buttonName : GetMessage('MOD_BACK')?>" />
		</p>
		</form>
		<?
		include($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin.php');
		die();
	}
}
?>
