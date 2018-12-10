<?
include_once str_replace('\\', '/', dirname(__FILE__)).'/install/index.php';

CModule::AddAutoloadClasses(
    demteam_currencyformat::MODULE_ID,
    array(
		"CDemteamCurrencyformat" => "classes/general/CDemteamCurrencyformat.php",
		"CDemteamCurrencyformatEvent" => "classes/general/CDemteamCurrencyformatEvent.php",
		"CDemteamCurrencyformatCommon" => "classes/general/CDemteamCurrencyformatCommon.php",
		"CDemteamCurrencyformatParameters" => "classes/general/CDemteamCurrencyformatParameters.php",
		"CDemteamCurrencyformatTemplate" => "classes/general/CDemteamCurrencyformatTemplate.php",
    )
);
?>
