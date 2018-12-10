<?
/* Класс для работы с параметрами в битриксе
 * хранит в себе сами параметры,
 * функцию для подстановки текущих значений в параметры
 * функцию для сохранения параметров, используемую на нужной странице после POST-запроса
 * функцию для формирования параметров с заголовками языков и сайтов для функции Common::DrawRow
 * функцию для получение значения параметра
 * TODO: добавить разделение параметров по аргументу функции: нужно для создания отдельных настроек тех же валют
 */
IncludeModuleLangFile(__FILE__);
class CDemteamCurrencyformatParameters
{
	const MODULE_ID = demteam_currencyformat::MODULE_ID;
	const PREFIX = demteam_currencyformat::PREFIX;
	const NORMAL = 'NORMAL';
	const BY_LANG = 'BY_LANG';
	const BY_SITE = 'BY_SITE';
	const BY_SITE_AND_LANG = 'BY_SITE_AND_LANG';
	
	// Функция возвращает список параметров по указанному типу и табу
	private function &Parameters($type=NULL)
	{
		/* Вид parameters[$type] = array(params_type=>array(param1, paramN),...)
		 * где params_type:
		 *  NORMAL - простые параметры
		 *  BY_LANG - параметры, указываемые для каждого языка
		 *  BY_SITE - параметры, указываемые для каждого сайта
		 *  BY_SITE_AND_LANG - параметры, указываемые для каждого языка каждого сайта
		 *
		 *  Ключи параметра:
		 *   key        - название параметра. уникальность ключа по всем params_type в одном $type
		 *    LABEL     - надпись напротив параметра
		 *    TYPE*     - тип параметра (textarea, text, password, checkbox, selectbox, multiselectbox, statictext, statichtml)
		 *    TYPE_ADD1
		 *    TYPE_ADD2 - доп. параметры, применение - в функции Common::DrawRow
		 *    DEFAULT   - значение по умолчанию
		 *    VALUE     - текущее значение
		 *    DISABLED  - флаг параметр выключен
		 *    SUP_TEXT  - надстрочный красный текст после надписи
		 *    VALIDATE  - регулярное выражение для функции preg_match для проверки валидности строки
		 */
		static $parameters;
		if (is_array($parameters))
		{
			if ($type) if ($parameters[$type]) return $parameters[$type]; else return false;
			return $parameters;
		}
		
		$parameters['currency_advanced'] = array(
			self::NORMAL => array(
				GetMessage('DEMTEAM_CURRENCYFORMAT_BIGNOTE', 
					array(
						'#BASE_CLASS#'=>demteam_currencyformat::BASE_CLASS,
						'#CURRENCY_TEMPLATES#'=>CDemteamCurrencyformatTemplate::CURRENCY_TEMPLATES,
						'#DISABLE_CURRENCYFORMAT#'=>CDemteamCurrencyformat::DISABLE_CURRENCYFORMAT,
						'#BASE_TEMPLATE#'=>BX_ROOT.'/modules/'.self::MODULE_ID.'/'.CDemteamCurrencyformatTemplate::CURRENCY_TEMPLATES.'_v'.demteam_currencyformat::CurrencyVersion(),
						'#BASE_TEMPLATE_ENCODED#'=>urlencode(BX_ROOT.'/modules/'.self::MODULE_ID.'/'.CDemteamCurrencyformatTemplate::CURRENCY_TEMPLATES.'_v'.demteam_currencyformat::CurrencyVersion()),
						'#CURRENCY_FORMAT_ARGS_BY_VERSION#'=>demteam_currencyformat::CurrencyFormatParams(),
					)
				)
			),
			self::BY_LANG => array(),
			self::BY_SITE => array(),
			self::BY_SITE_AND_LANG => array(),
		);
		// Добавление префикса к названиям переменных
		foreach($parameters as &$parameters_by_type)
			foreach($parameters_by_type as &$parameters_by_params_type)
			{
				$new_parameters_by_params_type = array();
				foreach($parameters_by_params_type as $param_name=>&$param)
				{
					$new_param_name = self::PREFIX.$param_name;
					if (is_array($param))
						$new_parameters_by_params_type[$new_param_name] = $param;
					else
						$new_parameters_by_params_type[$new_param_name]['note'] = $param;
					$new_parameters_by_params_type[$new_param_name]['NAME'] = $new_param_name;
				}
				$parameters_by_params_type = $new_parameters_by_params_type;
			}
		
		if ($type) if ($parameters[$type]) return $parameters[$type]; else return false;
		return $parameters;
	}
	
	/* Функция возвращает массив параметров с заданными для них значениями с дополнительными заголовками языков и сайтов
	 * Если есть ошибки, описанные в функции _CheckErrors, то возвращает тексты ошибок
	 * В обоих случаях, массив пригоден для функции DrawRow
	 * Отдельный случай, когда надо создать вкладки для сайтов, вид массива вкладок:
	 *  array(site_id => array(NAME=>,ITEMS=>array(...)), ..)
	 */
	public function &GetParameters($type=NULL, $show_empty_parameters_error=false)
	{
		static $result;
		if (isset($result)) return $result;
	
		$parameters = &self::Parameters($type);
		if (!$parameters) { $result = false; return $result; }
		
		if($type)
		{
			// Сохранение параметров указанного типа
			$result = self::InitParametersType($parameters, $type, $show_empty_parameters_error);
		}
		else
		{
			// Сохранение параметров всех типов
			foreach($parameters as $type_name=>&$parameters_by_type)
				$result[$type_name] = self::InitParametersType($parameters_by_type, $type, $show_empty_parameters_error);
		}
		
		return $result;
	}
	
	// Функция задает значения параметров для одного набора параметров
	private function InitParametersType(&$parameters, &$type, &$show_empty_parameters_error)
	{
		$result = array('HAVE_ERRORS'=>false,'ITEMS'=>array());
	
		list($result['HAVE_ERRORS'], $result['ITEMS']) = self::_CheckErrors($parameters, $type, $show_empty_parameters_error);
		if ($result['HAVE_ERRORS']===true) return $result;
	
		$output_parameters = &$result['ITEMS'];
	
		// Получение значений простых параметров
		foreach($parameters[self::NORMAL] as &$param)
		{
			$param['VALUE'] = COption::GetOptionString(self::MODULE_ID, $param['NAME'], $param['DEFAULT']);
			$param['INPUT_NAME'] = $param['NAME'];
			$output_parameters[] = $param;
		}
		
		// Список языков
		if (count($parameters[self::BY_LANG]) || count($parameters[self::BY_SITE_AND_LANG]))
			$langs = &self::_langs();
		// Список сайтов
		if (count($parameters[self::BY_SITE]) || count($parameters[self::BY_SITE_AND_LANG]))
			$sites = &self::_sites();
		
		if (count($parameters[self::BY_LANG]) && count($langs))
		{
			// Получение значений параметров, указываемых для каждого языка
			foreach($langs as &$lang)
			{
				$output_parameters[] = $lang['NAME'];
				foreach($parameters[self::BY_LANG] as &$param)
				{
					$new_param_name = $param['NAME'].$lang['LID_FORMATTED'];
					$param['VALUE'] = COption::GetOptionString(self::MODULE_ID, $new_param_name, $param['DEFAULT']);
					$parma['INPUT_NAME'] = $new_param_name;
					$output_parameters[] = $param;
				}
			}
		}
		
		if (count($parameters[self::BY_SITE]) && count($sites))
		{
			// Получение значений параметров, указываемых для каждого сайта
			$subtabs = array();
			foreach($sites as &$site)
			{
				$items = array();
				foreach($parameters[self::BY_SITE] as &$param)
				{
					$new_param_name_wo_site = $param['NAME'];
					$new_param_name = $param['NAME'].$site['ID_OPTION'];
					$param['VALUE'] = COption::GetOptionString(self::MODULE_ID, $new_param_name_wo_site, $param['DEFAULT'], $site['ID'], true);
					$param['INPUT_NAME'] = $new_param_name;
					$items[] = $param;
				}
				$subtabs[$site['ID']] = array('NAME' => $site['ID_FORMATTED'].$site['NAME'], 'ITEMS' => $items);
			}
			$output_parameters[] = array('SUBTABS' => $subtabs);
			end($output_parameters); $subtabs_key = key($output_parameters);
		}

		if (count($parameters[self::BY_SITE_AND_LANG]) && count($sites))
		{
			// Получение значений параметров, указываемых для каждого языка каждого сайта
			if ($subtabs_key===NULL) $subtabs = array();
			foreach($sites as &$site)
			{
				$items = array();
				foreach($langs as &$lang)
				{
					$items[] = $lang['NAME'];
					foreach($parameters[self::BY_SITE_AND_LANG] as &$param)
					{
						$new_param_name_wo_site = $param['NAME'].$lang['LID_FORMATTED'];
						$new_param_name = $param['NAME'].$lang['LID_FORMATTED'].$site['ID_OPTION'];
						$param['VALUE'] = COption::GetOptionString(self::MODULE_ID, $new_param_name_wo_site, $param['DEFAULT'], $site['ID'], true);
						$param['INPUT_NAME'] = $new_param_name;
						$items[] = $param;					
					}
				}
				if ($subtabs_key===NULL)
				{
					$subtabs[$site['ID']] = array('NAME' => $site['ID_FORMATTED'].$site['NAME'], 'ITEMS' => $items);
				}
				else
				{
					$subtabs[$site['ID']]['ITEMS'] = array_merge($subtabs[$site['ID']]['ITEMS'], $items);
				}
			}
			if ($subtabs_key===NULL)
			{
				$output_parameters[] = array('SUBTABS' => $subtabs);
			}
			else
			{
				$output_parameters[$subtabs_key]['SUBTABS'] = $subtabs;
			}
			
		}
		
		return $result;
	}
	
	// Функция получает значение параметра, в зависимости от типа параметра (по текущему сайту или языку)
	public function GetParameterValue($param_name, $type)
	{
		static $params_values;
		if (array_key_exists($param_name, $params_values)) return $params_values[$param_name];
		
		if ($type === NULL) { $params_values[$param_name] = NULL; return $params_values[$param_name]; }
		$parameters = &self::Parameters($type);
		if (!$parameters) { $params_values[$param_name] = NULL; return $params_values[$param_name]; }
		
		// Создание одномерного массива key=>value, где key - название параметра, value = params_type
		static $params_type_params;
		if (!is_array($params_type_params))
		{
			$params_type_params = array();
			foreach($parameters as $params_type=>&$parameters_by_params_type)
				foreach($paramters_by_params_type as &$param)
					$params_type_params[$param['NAME']] = $params_type;
		}
		
		// Смотрим есть ли указанный параметр
		$adjusted_param_name = self::PREFIX.$param_name;
		if (array_key_exists($adjusted_param_name, $params_type_params))
		{
			$params_type = &$params_type_params[$adjusted_param_name];
			$param = &$parameters[$params_type][$adjusted_param_name];
			$param_name_wo_site = NULL;
			$bExactSite = false;
			// По типу параметра определяем его имя в настройках
			if ($params_type==self::NORMAL)
			{
				$param_name_wo_site = $param['NAME'];
				$site_id = "";
			}
			elseif ($params_type==self::BY_LANG)
			{
				$lid_formatted = self::_lid_formatted(LANGUAGE_ID);
				$param_name_wo_site = $param['NAME'].$lid_formatted;
				$site_id = "";
			}
			elseif ($params_type==self::BY_SITE)
			{
				$param_name_wo_site = $param['NAME'];
				$site_id = SITE_ID;
				$bExactSite = true;
			}
			elseif ($params_type==self::BY_SITE_AND_LANG)
			{
				$lid_formatted = self::_lid_formatted(LANGUAGE_ID);
				$param_name_wo_site = $param['NAME'].$lid_formatted;
				$site_id = SITE_ID;
				$bExactSite = true;
			}
			
			if ($param_name_wo_site!==NULL)
				// Получаем значение
				$params_values[$param_name] = COption::GetOptionString(self::MODULE_ID, $param_name_wo_site, $param['DEFAULT'], $site_id, $bExactSite);
			else
				$params_values[$param_name] = NULL;
			return $params_values[$param_name];
		}
		
	}
	
	/* Функция сохраняет параметры, которые пришли в массиве $_POST
	 * Охватываемые параметры задаются аргументами:
	 *  type - тип параметров
	 */
	public function SaveParameters($type=NULL)
	{
		$parameters = &self::Parameters($type);
		if (!$parameters) return false;
		
		if($type)
		{
			// Сохранение параметров указанного типа
			self::SaveParametersType($parameters);
		}
		else
		{
			// Сохранение параметров всех типов
			foreach($parameters as &$parameters_by_type)
				self::SaveParametersType($parameters_by_type);
		}
	}
	
	
	// Функция сохраняет параметры одного таба
	private function SaveParametersType(&$parameters)
	{
		// Сохранение простых параметров
		foreach($parameters[self::NORMAL] as &$param)
			self::SaveParameterEntity($param['NAME'], $param);
		
		// Список языков
		if (count($parameters[self::BY_LANG]) || count($parameters[self::BY_SITE_AND_LANG]))
			$langs = &self::_langs();
		// Список сайтов
		if (count($parameters[self::BY_SITE]) || count($parameters[self::BY_SITE_AND_LANG]))
			$sites = &self::_sites();
		
		// Сохранение параметров, указываемых для каждого языка
		foreach($parameters[self::BY_LANG] as &$param)
			foreach($langs as &$lang)
				self::SaveParameterEntity($param['NAME'].$lang['LID_FORMATTED'], $param);
		
		// Сохранение параметров, указываемых для каждого сайта
		foreach($parameters[self::BY_SITE] as &$param)
			foreach($sites as &$site)
				self::SaveParameterEntity($param['NAME'], $param, $site['ID']);

		// Сохранение параметров, указываемых для каждого языка каждого сайта
		foreach($parameters[self::BY_SITE_AND_LANG] as &$param)
			foreach($sites as &$site)
				foreach($langs as &$lang)
					self::SaveParameterEntity($param['NAME'].$lang['LID_FORMATTED'], $param, $site['ID']);

	}
	
	
	// Функция-обертка для SaveParameter
	// Используется в SaveParametersType
	private function SaveParameterEntity($param_name, &$param, $site_id=false)
	{
		$val = $site_id===false? $_POST[$param_name]: $_POST[$param_name][$site_id];
	
		if (!self::_TextParameter($param) && self::ValidateParameter($param, $val))
			self::SaveParameter($param_name, $param, $val, $site_id);
	}
	
	
	// Функция проверяет корректность значения указанного параметра регулярным выражением
	private function ValidateParameter(&$param, $val)
	{
		return $param['VALIDATE']? preg_match($param['VALIDATE'], $val): true;		
	}
	
	/* Функция сохраняет указанный параметр
	 * Вызывается через ее функцию-обертку SaveParameterEntity
	 * За основу взят код /bitrix/modules/main/admin/settings.php __AdmSettingsSaveOption
	 */
	private function SaveParameter($param_name, &$param, $val, $site_id=false)
	{
		if (self::_TextParameter($param))
			return false;
		
		if($param['TYPE'] == "selectbox" && !@array_key_exists($val, $param['TYPE_ADD1']))
			return false;
		
		if($param['TYPE'] == "multiselectbox")
		{
			if (!is_array($val)) return false;
			if (count($val))
			{
				foreach($val as $key=>&$one_val)
					if (!@array_key_exists($one_val, $param['TYPE_ADD1']))
						unset($val[$key]);
				if (!count($val)) return false;
			}
		}
		
		//disabled
		if(!isset($val))
		{
			if($param['TYPE'] == 'checkbox')
				$val = 'N';
			else
				$val = '';
		}
		
		if($param['TYPE'] == "checkbox" && $val != "Y")
			$val = "N";
		
		if($param['TYPE'] == "multiselectbox")
			$val = @implode(",", $val);

		COption::SetOptionString(self::MODULE_ID, $param_name, (string)$val, $param['LABEL'], $site_id===false?"":$site_id);
	}
	
	
	// Функция удаляет параметры из БД, завязанные на указанном сайте
	public function DeleteSiteParameters($site_id)
	{
		$parameters = &self::Parameters();
		foreach($parameters as &$parameters_by_type)
		{
			// Удаление простых параметров сайта
			foreach($parameters_by_type[self::BY_SITE] as &$param)
				COption::RemoveOption(self::MODULE_ID, $param['NAME'], $site_id);
			
			// Удаление параметров сайта каждого языка
			if (count($parameters_by_type[self::BY_SITE_AND_LANG]))
			{
				$langs = &self::_langs();
				foreach($langs as &$lang)
					foreach($parameters_by_type[self::BY_SITE_AND_LANG] as &$param)
						COption::RemoveOption(self::MODULE_ID, $param['NAME'].$lang['LID_FORMATTED'], $site_id);
			}
		}
	}
	
	// Функция удаляет параметры из БД, завязанные на указанном языке
	public function DeleteLanguageParameters($language_id)
	{
		$lid_formatted = self::_lid_formatted($language_id);
		$parameters = &self::Parameters();

		foreach($parameters as &$parameters_by_type)
		{
			// Удаление простых параметров языка
			foreach($parameters_by_type[self::BY_LANG] as &$param)
				COption::RemoveOption(self::MODULE_ID, $param['NAME'].$lid_formatted);
			
			// Удаление параметров языка каждого сайта
			if (count($parameters_by_type[self::BY_SITE_AND_LANG]))
			{
				$sites = &self::_sites();
				foreach($sites as &$site)
					foreach($parameters_by_type[self::BY_SITE_AND_LANG] as &$param)
					{
						COption::RemoveOption(self::MODULE_ID, $param['NAME'].$lid_formatted, $site['ID']);
					}
			}
		}
	}
	
	// Функция удаляет все параметры
	// Используется при удалении модуля
	public function DeleteAllParameters()
	{
		COption::RemoveOption(self::MODULE_ID);
	}
	
	
	// Вспомогательная функция, возвращает true, если параметр является текстом для вывода в функции Common::DrawRow
	private function _TextParameter(&$param)
	{
		return !is_array($param) || isset($param['note']) || $param['TYPE']=="statictext" || $param['TYPE'] == "statichtml";
	}
	
	// Вспомогательная функция для функции GetParameters
	// Создает массив с текстами имеющихся ошибок
	private function _CheckErrors(&$parameters, &$type, &$show_empty_parameters_error)
	{
		foreach(array(self::NORMAL,self::BY_LANG,self::BY_SITE,self::BY_SITE_AND_LANG) as $params_type)
			$length[$params_type] = count($parameters[$params_type]);

		// Не указаны параметры
		if (!$length[self::NORMAL] && !$length[self::BY_LANG] && !$length[self::BY_SITE] && !$length[self::BY_SITE_AND_LANG])
		{
			if ($show_empty_parameters_error)
			{
				// Параметр текст "нет опций"
				$arOption = array('note' => self::_GetMessageByType('DEMTEAM_CURRENCYFORMAT_NO_OPTIONS', $type));
				return array(true, array($arOption));
			}
			else
			{
				return array(true);
			}			
		}
		
		
		// Указаны только параметры для каждого языка
		if (!$length[self::NORMAL] && !$length[self::BY_SITE] && ($length[self::BY_LANG] || $length[self::BY_SITE_AND_LANG]))
		{
			$langs = &self::_langs(); // Список языков
			if (!count($langs))
			{
				// Параметр текст "нет языков"
				$arNoLanguagesOption = array('note' => self::_GetMessageByType('DEMTEAM_CURRENCYFORMAT_NO_LANGUAGES', $type));
			
				// Нет языков
				if ($length[self::BY_SITE_AND_LANG])
				{
					// Указаны параметры для каждого языка каждого сайта
					$items = array($arNoLanguagesOption);
					$sites = &self::_sites(); // Список сайтов
					if (count($sites))
					{
						// Есть сайты
						$subtabs = array();
						foreach($sites as &$site)
						{
							$subtabs[$site['ID']] = array('NAME' => $site['ID_FORMATTED'].$site['NAME'], 'ITEMS' => $items);
						}
						return array(true, array(array('SUBTABS'=>$subtabs)));
					}
					else
					{
						// Параметр текст "нет сайтов"
						$arNoSitesOption = array('note' => self::_GetMessageByType('DEMTEAM_CURRENCYFORMAT_NO_SITES', $type));
						// Нет сайтов
						return array(true, array($arNoSitesOption, $arNoLanguagesOption));
					}
				}
				else
				{
					// Указаны параметры для каждого языка
					return array(true, array($arNoLanguagesOption));
				}
			}
		}
	
		// Указаны только параметры для каждого сайта и нет сайтов
		if (!$length[self::NORMAL] && !$length[self::BY_LANG] && ($length[self::BY_SITE] || $length[self::BY_SITE_AND_LANG]))
		{
			$sites = &self::_sites(); // Список сайтов
			if (!count($sites))
			{
				// Параметр текст "нет сайтов"
				$arNoSitesOption = array('note' => self::_GetMessageByType('DEMTEAM_CURRENCYFORMAT_NO_SITES', $type));
				return array(true, array($arNoSitesOption));
			}
		}
		
		return array(false, array());
	}
	
	// Вспомогательная функция, возвращает по коду соответствующее сообщение на текущем языке, учитывая тип и таб
	private function _GetMessageByType($name, &$type, $aReplace=false)
	{
		$s = GetMessage($name.'_'.$type, $aReplace);
		if ($s===NULL) $s = GetMessage($name, $aReplace);
		return $s;
	}
	
	// Вспомогательная функция, возвращает список языков
	private function &_langs()
	{
		static $langs;
		if (is_array($langs)) return $langs;
		
		$rsLangs = CLangAdmin::GetList($by = "sort", $order = "asc");
		while ($arRes = $rsLangs->GetNext())
			$langs[] = array('LID_FORMATTED'=>'_'.$arRes['LID'], 'NAME'=>$arRes['NAME']);
			
		return $langs;
	}
	
	// Вспомогательная функция, преобразует ид языка для подстановки в название параметра
	// Используется, когда нужно работать с параметрами по указанному языку (DeleteLanguageOptions)
	private function _lid_formatted($language_id)
	{
		return '_'.$language_id;
	}
	
	// Вспомогательная функция, возвращает список сайтов
	private function &_sites()
	{
		static $sites;
		if (is_array($sites)) return $sites;
		
		$rsSites = CSite::GetList($by='sort', $order='asc');
		while($arRes = $rsSites->GetNext())
			$sites[] = array('ID'=>$arRes['ID'], 'ID_OPTION'=>'['.$arRes['ID'].']', 'ID_FORMATTED' => '('.$arRes['ID'].') ', 'NAME'=>$arRes['NAME']);
			
		return $sites;
	}	
}
