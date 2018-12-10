<?
IncludeModuleLangFile(__FILE__);

/* Класс для работы с шаблоном формата валюты */
class CDemteamCurrencyformatTemplate
{
	const MODULE_ID = demteam_currencyformat::MODULE_ID;
	const PREFIX = demteam_currencyformat::PREFIX;
	const CURRENCY_TEMPLATES = 'currency_templates';
	
	// Функция инициализирует шаблон валюты при первом вызове
	// И всегда выполняет вывод шаблона
	// \bitrix\modules\main\classes\general\component_template.php:172
	public function IncludeTemplate($currency_args, $templateName='', $site_id=false, $lang=false)
	{
		static $templates;
		$template_key = $templateName.$site_id;
		if (!array_key_exists($template_key, $templates))
			$templates[$template_key] = self::_SearchTemplatesFolder($templateName, $site_id);
		
		return $templates[$template_key]===false? false: self::_process_template($currency_args, $templates[$template_key]['path'], $templates[$template_key]['site'], $lang);
	}
	
	
	
	/* Вспомогательная функция, служит для поиска папки с шаблонами валюты
	 * Приоритет поиска:
	 * 	/local/templates/<шаблон сайта>/currency_templates
	 *  /local/templates/.default/currency_templates
	 *  /bitrix/templates/<шаблон сайта>/currency_templates
	 *  /bitrix/templates/.default/currency_templates
	 */
	private function _SearchTemplatesFolder(&$templateName, &$site_id)
	{
		$defTemplate = '.default';
		// Инициализация шаблона
		if ($site_id === false && defined("SITE_TEMPLATE_ID"))
			$site_id = SITE_TEMPLATE_ID;
		elseif (!strlen($site_id))
			$site_id = $defTemplate;
		
		if (!strlen($templateName)) $templateName = $defTemplate;
	
		
		$relativePath = '/'.self::CURRENCY_TEMPLATES;
		
		$defSiteTemplate = ($site_id == $defTemplate);
		$dirs = array();
		// Указываем пути для поиска шаблонов валюты
		if(!$defSiteTemplate) $dirs[] = array('path'=>"/local/templates/".$site_id.$relativePath, 'site'=>$site_id);
		$dirs[] = array('path'=>"/local/templates/".$defTemplate.$relativePath, 'site'=>$defTemplate);
		if(!$defSiteTemplate) $dirs[] = array('path'=>BX_PERSONAL_ROOT."/templates/".$site_id.$relativePath, 'site'=>$site_id);
		$dirs[] = array('path'=>BX_PERSONAL_ROOT."/templates/".$defTemplate.$relativePath, 'site'=>$defTemplate);

		// Ищем директорию с шаблоном
		foreach ($dirs as &$dir)
		{
			$absDir = $_SERVER["DOCUMENT_ROOT"].$dir['path'];
			if (file_exists($absDir) && is_dir($absDir) && ($templateFolder = self::_SearchTemplateFolder($templateName, $dir['path'])))
				return array('path'=>$templateFolder, 'site'=>$dir['site']);
		}
		return false;
	}
	
	/* Вспомогательная функция, служит для поиска папки с шаблоном валюты
	 * Приоритет поиска:
	 *  <папка с шаблонами>/<шаблон валюты>
	 *  <папка с шаблонами>/.default
	 */
	private function _SearchTemplateFolder(&$templateName, &$main_dir)
	{
		$defTemplate = '.default';
		$defTemplateName = ($templateName == $defTemplate);
		$dirs = array();
		$dirs[] = $templateName;
		if (!$defTemplateName) $dirs[] = $defTemplate;
		
		foreach($dirs as &$dir)
		{
			$rel_dir = $main_dir.'/'.$dir;
			$absDir = $_SERVER['DOCUMENT_ROOT'].$rel_dir;
			if (file_exists($absDir) && is_dir($absDir))
				return $rel_dir;
		}
	}

	
	/* Вспомогательная функция, выполняет команду eval() над файлом шаблона template.php
	 * Файл кешируется для повторного использования
	 * Подключает файлы style.css и script.js и языковой файл
	 * При первом запуске также выполняет скрипт template_prolog.php
	 */
	private function _process_template(&$currency_args, &$template_path, &$site_id, &$lang)
	{
		static $eval_codes;
		if (!array_key_exists($template_path, $eval_codes))
		{
			$template_file = '/template.php';
			$absTemplate_path = $_SERVER['DOCUMENT_ROOT'].$template_path;
			$filepath = $absTemplate_path.$template_file;
			if (file_exists($filepath))
			{
				$eval_codes[$template_path] = '?><?php unset($code)?>'.self::_read_file($filepath).'<?php ';
				// Подключаем файлы style.css и script.js
				if (file_exists($absTemplate_path.'/style.css')) $GLOBALS['APPLICATION']->SetAdditionalCSS($template_path.'/style.css');
				if (file_exists($absTemplate_path.'/script.js')) $GLOBALS['APPLICATION']->AddHeadScript($template_path.'/script.js');
				// Подключаем языковой файл
				self::IncludeLangFile($template_file, $absTemplate_path, $lang);
				// Подключаем пролог к шаблону один раз
				if (file_exists($absTemplate_path.'/template_prolog.php')) self::_include_file($currency_args, $absTemplate_path.'/template_prolog.php', $template_path, $site_id, $lang);
			}
			else
			{
				$eval_codes[$template_path] = NULL;
			}
		}
		
		return self::_eval_php($currency_args, $template_path, $eval_codes[$template_path], $template_path, $site_id, $lang);
	}
	
	// Вспомогательная функция, читает php-файл
	private function _read_file($filepath)
	{
		$content = file_get_contents($filepath);
		if (function_exists('token_get_all'))
		{
			$tokens = token_get_all($content); $open_tag = false; $new_content = '';
			$realpath = realpath($filepath); $constant = array('file'=>'"'.$realpath.'"', 'dir'=>'"'.dirname($realpath).'"');
			// Скрипт заменяет "волшебные константы" __FILE__ и __DIR__ на соответствующие строки
			// и добавляет закрывающий php-тэг, если пропущен
			foreach($tokens as &$token)
			{
				if (is_string($token)) $new_content .= $token;
				else
				{
					// http://www.php.net/manual/ru/tokens.php
					if ($token[0]== T_OPEN_TAG) $open_tag = true;
					elseif ($token[0] == T_CLOSE_TAG) {if($open_tag) $open_tag = false;}
					elseif ($token[0] == T_FILE) $token[1] = $constant['file'];
					elseif ($token[0] == T_DIR) $token[1] = $constant['dir'];
					elseif ($token[0] == T_COMMENT || $token[0] == T_DOC_COMMENT) continue;
					$new_content .= $token[1];
				}
			}
			if ($open_tag) $new_content.='?>';
			$content = $new_content;
		}
		return $content;
	}
	
	/* Вспомогательная функция для выполнения php кода */
	private function _eval_php($currency_args, $templateFile, &$code, $templateFolder, $site_id, $lang)
	{
		if ($lang===false) $lang=LANGUAGE_ID;
		extract($currency_args); unset($currency_args);
		ob_start();
		eval($code);
		return ob_get_clean();
	}
	
	/* Вспомогательная функция для одноразового выполнения php-файлов */
	private function _include_file($currency_args, $prologFile, $templateFolder, $site_id, $lang)
	{
		global $APPLICATION;
		if ($lang===false) $lang=LANGUAGE_ID;
		extract($currency_args); unset($currency_args);
		include $prologFile;
	}
	
	
	
	/* Функция подключает языковой файл шаблона вылюты
	 * Взято с CBitrixComponentTemplate::IncludeLangFile \bitrix\modules\main\classes\general\component_template.php:554
	 */
	private function IncludeLangFile(&$template_file, &$template_path, &$lang)
	{
		$absPath = $template_path.'/lang/';
		if ($lang === false) $lang = LANGUAGE_ID;
		
		$subst_lang = LangSubst($lang);
		if ($subst_lang<>$lang)
			__IncludeLang($absPath.$subst_lang.$template_file);
		
		__IncludeLang($absPath.$lang.$template_file);
	}
}