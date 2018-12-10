<?
IncludeModuleLangFile(__FILE__);

/* Класс для публичных настроек */
class CDemteamCurrencyformat
{
	const SEP_EMPTY = 'N';
	const SEP_DOT = 'D';
	const SEP_COMMA = 'C';
	const SEP_SPACE = 'S';
	const SEP_NBSPACE = 'B';

	static protected $arSeparators = array(
		SEP_EMPTY => '',
		SEP_DOT => '.',
		SEP_COMMA => ',',
		SEP_SPACE => ' ',
		SEP_NBSPACE => ' '
	);

	static protected $arDefaultValues = array(
		'FORMAT_STRING' => '#',
		'DEC_POINT' => '.',
		'THOUSANDS_SEP' => ' ',
		'DECIMALS' => 2,
		'THOUSANDS_VARIANT' => '',
		'HIDE_ZERO' => 'N'
	);

	
	const DISABLE_CURRENCYFORMAT = 'DISABLE_CURRENCYFORMAT_TEMPLATE';

	static private $templateName = false;
	static private $site_id = false;
	static private $lang = false;
	
	// Функция для смены шаблона форматирования валюты
	public function SetTemplate($templateName = false, $site_id = false, $lang = false)
	{
		foreach(array('templateName','site_id','lang') as $var)
		{
			if (${$var}===self::${$var} || ${$var}===NULL) continue;
			if (${$var}===false || preg_match("#^([A-Za-z0-9_.-]+)?$#i", ${$var})) self::${$var} = ${$var};
		}
	}
	
	// Фунция для события вызываемого при форматировании валюты
    public function CurrencyFormat($param1, $param2)
    {
		if(defined('ADMIN_SECTION') && ADMIN_SECTION === true) return;		
		if ($GLOBALS['APPLICATION']->GetPageProperty(self::DISABLE_CURRENCYFORMAT)=="Y") return;

		if (demteam_currencyformat::CurrencyVersion()==14) $currency_args = array('price'=>$param1,'currency'=>$param2);
			else $currency_args = array('fSum'=>$param1,'strCurrency'=>$param2);
		
		$result = CDemteamCurrencyformatTemplate::IncludeTemplate($currency_args, self::$templateName, self::$site_id, self::$lang);
		return $result!==false? (strlen($result)? $result: ' '): NULL;
    }
	
	// Функция для получения переменной $arCurFormat для вызова из шаблона
	public function GetCurrencyFormat($currency, $lang = LANGUAGE_ID)
	{
		static $arCurrencyFormat;
		$key = $currency.'|'.$lang;
		if (array_key_exists($key, $arCurrencyFormat)) return $arCurrencyFormat[$key];
		
		$arCurFormat = &$arCurrencyFormat[$key];
		$arCurFormat = CCurrencyLang::GetCurrencyFormat($currency, $lang);
		$CurrencyVersion = demteam_currencyformat::CurrencyVersion();
		$arDefaultValues = $CurrencyVersion==14? CCurrencyLang::GetDefaultValues(): self::$arDefaultValues;
		if (false === $arCurFormat)
		{
			$arCurFormat = $arDefaultValues;
		}
		else
		{
			if (!isset($arCurFormat['DECIMALS']))
				$arCurFormat['DECIMALS'] = $arDefaultValues['DECIMALS'];
			$arCurFormat['DECIMALS'] = intval($arCurFormat['DECIMALS']);
			if (!isset($arCurFormat['DEC_POINT']))
				$arCurFormat['DEC_POINT'] = $arDefaultValues['DEC_POINT'];
			if (!empty($arCurFormat['THOUSANDS_VARIANT']) && isset(self::$arSeparators[$arCurFormat['THOUSANDS_VARIANT']]))
			{
				$arCurFormat['THOUSANDS_SEP'] = self::$arSeparators[$arCurFormat['THOUSANDS_VARIANT']];
			}
			elseif (!isset($arCurFormat['THOUSANDS_SEP']))
			{
				$arCurFormat['THOUSANDS_SEP'] = $arDefaultValues['THOUSANDS_SEP'];
			}
			if (!isset($arCurFormat['FORMAT_STRING']))
			{
				$arCurFormat['FORMAT_STRING'] = $arDefaultValues['FORMAT_STRING'];
			}
			if ($CurrencyVersion==14)
			{
				if (!isset($arCurFormat['HIDE_ZERO']) || empty($arCurFormat['HIDE_ZERO']))
					$arCurFormat['HIDE_ZERO'] = $arDefaultValues['HIDE_ZERO'];
			}
		}
		return $arCurFormat;
	}

}
?>