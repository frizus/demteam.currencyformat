<?
$MESS['DEMTEAM_CURRENCYFORMAT_NO_OPTIONS_currency_advanced'] = 'Настройки шаблонов форматирования валюты отсутствуют.';
$MESS['DEMTEAM_CURRENCYFORMAT_NO_LANGUAGES_currency_advanced'] = 'Настройки шаблонов форматирования валюты устанавливаются индивидуально для <a href="'.BX_ROOT.'/admin/lang_admin.php?lang='.LANGUAGE_ID.'">каждого языка</a>. Должен быть создан хотя бы один язык.';
$MESS['DEMTEAM_CURRENCYFORMAT_NO_SITES_currency_advanced'] = 'Настройки шаблонов форматирования валюты устанавливаются индивидуально для <a href="'.BX_ROOT.'/admin/site_admin.php?lang='.LANGUAGE_ID.'">каждого сайта</a>. Должен быть создан хотя бы один сайт.';

$BASE_TEMPLATE = BX_ROOT.'/admin/fileman_admin.php?lang='.LANGUAGE_ID.'&path=#BASE_TEMPLATE_ENCODED#';
$note = '';
$note .= '<div align="left">';
$note .= 'Для свободного форматирования валюты используются шаблоны.<br>';
$note .= '<p>С модулем идут <a href="'.$BASE_TEMPLATE.'" target="_blank">готовые шаблоны</a>.<br>';
$note .= 'Для подключения шаблона, нужно скопировать его в папку для хранения шаблонов и переименовать в <i>.default</i>.<br>';
$note .= '</p>';
$note .= '<p>Папки для хранения шаблонов следующие:<br>';
$note .= '<pre>
/local/templates/&lt;шаблон сайта&gt;/#CURRENCY_TEMPLATES#/&lt;шаблон валюты&gt;
'.BX_PERSONAL_ROOT.'/templates/&lt;шаблон сайта&gt;/#CURRENCY_TEMPLATES#/&lt;шаблон валюты&gt;
</pre>';
$note .= '</p>';
$note .= '<p>По умолчанию используется шаблон валюты из папки <i>.default</i>.<br>';
$note .= 'Если валюта форматируется не штатным способом, то нужно ручное форматирование.</p>';
$note .= '<p><br></p>';
$note .= '<p>Создание шаблона:</p>';
$note .= '<p>Создание шаблона валюты аналогично созданию шаблона компонента.<br>';
$note .= 'Для примера можно взять один из <a href="'.$BASE_TEMPLATE.'" target="_blank">готовых шаблонов</a> валюты из модуля.</p>';
$note .= '<p>Структура папки шаблона валюты:<pre>
template.php
template_prolog.php
style.css
script.js
lang/&lt;язык&gt;/template.php
</pre></p>';
$note .= '<p>Файл <i>template_prolog.php</i> используется для подключения дополнительных стилей и скриптов.</p>';
$note .= '<p><br></p>';
$note .= '<p>Доступные команды:';
$note .= '<pre>
$APPLICATION->SetPageProperty(\'#DISABLE_CURRENCYFORMAT#\', \'Y\');</code> &ndash; отключить шаблоны валюты
#BASE_CLASS#::SetTemplate($templateName, $site_id, $lang);</code> &ndash; сменить используемый шаблон на странице
#BASE_CLASS#::CurrencyFormat(#CURRENCY_FORMAT_ARGS_BY_VERSION#); - ручное форматирование суммы
</pre>';
$note .= '</p>';

$note .= '</div>';
		
$MESS['DEMTEAM_CURRENCYFORMAT_BIGNOTE'] = $note;