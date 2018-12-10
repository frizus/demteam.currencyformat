<?
/* ����� ��� ������ � ����������� � ��������
 * ������ � ���� ���� ���������,
 * ������� ��� ����������� ������� �������� � ���������
 * ������� ��� ���������� ����������, ������������ �� ������ �������� ����� POST-�������
 * ������� ��� ������������ ���������� � ����������� ������ � ������ ��� ������� Common::DrawRow
 * ������� ��� ��������� �������� ���������
 * TODO: �������� ���������� ���������� �� ��������� �������: ����� ��� �������� ��������� �������� ��� �� �����
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
	
	// ������� ���������� ������ ���������� �� ���������� ���� � ����
	private function &Parameters($type=NULL)
	{
		/* ��� parameters[$type] = array(params_type=>array(param1, paramN),...)
		 * ��� params_type:
		 *  NORMAL - ������� ���������
		 *  BY_LANG - ���������, ����������� ��� ������� �����
		 *  BY_SITE - ���������, ����������� ��� ������� �����
		 *  BY_SITE_AND_LANG - ���������, ����������� ��� ������� ����� ������� �����
		 *
		 *  ����� ���������:
		 *   key        - �������� ���������. ������������ ����� �� ���� params_type � ����� $type
		 *    LABEL     - ������� �������� ���������
		 *    TYPE*     - ��� ��������� (textarea, text, password, checkbox, selectbox, multiselectbox, statictext, statichtml)
		 *    TYPE_ADD1
		 *    TYPE_ADD2 - ���. ���������, ���������� - � ������� Common::DrawRow
		 *    DEFAULT   - �������� �� ���������
		 *    VALUE     - ������� ��������
		 *    DISABLED  - ���� �������� ��������
		 *    SUP_TEXT  - ����������� ������� ����� ����� �������
		 *    VALIDATE  - ���������� ��������� ��� ������� preg_match ��� �������� ���������� ������
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
		// ���������� �������� � ��������� ����������
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
	
	/* ������� ���������� ������ ���������� � ��������� ��� ��� ���������� � ��������������� ����������� ������ � ������
	 * ���� ���� ������, ��������� � ������� _CheckErrors, �� ���������� ������ ������
	 * � ����� �������, ������ �������� ��� ������� DrawRow
	 * ��������� ������, ����� ���� ������� ������� ��� ������, ��� ������� �������:
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
			// ���������� ���������� ���������� ����
			$result = self::InitParametersType($parameters, $type, $show_empty_parameters_error);
		}
		else
		{
			// ���������� ���������� ���� �����
			foreach($parameters as $type_name=>&$parameters_by_type)
				$result[$type_name] = self::InitParametersType($parameters_by_type, $type, $show_empty_parameters_error);
		}
		
		return $result;
	}
	
	// ������� ������ �������� ���������� ��� ������ ������ ����������
	private function InitParametersType(&$parameters, &$type, &$show_empty_parameters_error)
	{
		$result = array('HAVE_ERRORS'=>false,'ITEMS'=>array());
	
		list($result['HAVE_ERRORS'], $result['ITEMS']) = self::_CheckErrors($parameters, $type, $show_empty_parameters_error);
		if ($result['HAVE_ERRORS']===true) return $result;
	
		$output_parameters = &$result['ITEMS'];
	
		// ��������� �������� ������� ����������
		foreach($parameters[self::NORMAL] as &$param)
		{
			$param['VALUE'] = COption::GetOptionString(self::MODULE_ID, $param['NAME'], $param['DEFAULT']);
			$param['INPUT_NAME'] = $param['NAME'];
			$output_parameters[] = $param;
		}
		
		// ������ ������
		if (count($parameters[self::BY_LANG]) || count($parameters[self::BY_SITE_AND_LANG]))
			$langs = &self::_langs();
		// ������ ������
		if (count($parameters[self::BY_SITE]) || count($parameters[self::BY_SITE_AND_LANG]))
			$sites = &self::_sites();
		
		if (count($parameters[self::BY_LANG]) && count($langs))
		{
			// ��������� �������� ����������, ����������� ��� ������� �����
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
			// ��������� �������� ����������, ����������� ��� ������� �����
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
			// ��������� �������� ����������, ����������� ��� ������� ����� ������� �����
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
	
	// ������� �������� �������� ���������, � ����������� �� ���� ��������� (�� �������� ����� ��� �����)
	public function GetParameterValue($param_name, $type)
	{
		static $params_values;
		if (array_key_exists($param_name, $params_values)) return $params_values[$param_name];
		
		if ($type === NULL) { $params_values[$param_name] = NULL; return $params_values[$param_name]; }
		$parameters = &self::Parameters($type);
		if (!$parameters) { $params_values[$param_name] = NULL; return $params_values[$param_name]; }
		
		// �������� ����������� ������� key=>value, ��� key - �������� ���������, value = params_type
		static $params_type_params;
		if (!is_array($params_type_params))
		{
			$params_type_params = array();
			foreach($parameters as $params_type=>&$parameters_by_params_type)
				foreach($paramters_by_params_type as &$param)
					$params_type_params[$param['NAME']] = $params_type;
		}
		
		// ������� ���� �� ��������� ��������
		$adjusted_param_name = self::PREFIX.$param_name;
		if (array_key_exists($adjusted_param_name, $params_type_params))
		{
			$params_type = &$params_type_params[$adjusted_param_name];
			$param = &$parameters[$params_type][$adjusted_param_name];
			$param_name_wo_site = NULL;
			$bExactSite = false;
			// �� ���� ��������� ���������� ��� ��� � ����������
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
				// �������� ��������
				$params_values[$param_name] = COption::GetOptionString(self::MODULE_ID, $param_name_wo_site, $param['DEFAULT'], $site_id, $bExactSite);
			else
				$params_values[$param_name] = NULL;
			return $params_values[$param_name];
		}
		
	}
	
	/* ������� ��������� ���������, ������� ������ � ������� $_POST
	 * ������������ ��������� �������� �����������:
	 *  type - ��� ����������
	 */
	public function SaveParameters($type=NULL)
	{
		$parameters = &self::Parameters($type);
		if (!$parameters) return false;
		
		if($type)
		{
			// ���������� ���������� ���������� ����
			self::SaveParametersType($parameters);
		}
		else
		{
			// ���������� ���������� ���� �����
			foreach($parameters as &$parameters_by_type)
				self::SaveParametersType($parameters_by_type);
		}
	}
	
	
	// ������� ��������� ��������� ������ ����
	private function SaveParametersType(&$parameters)
	{
		// ���������� ������� ����������
		foreach($parameters[self::NORMAL] as &$param)
			self::SaveParameterEntity($param['NAME'], $param);
		
		// ������ ������
		if (count($parameters[self::BY_LANG]) || count($parameters[self::BY_SITE_AND_LANG]))
			$langs = &self::_langs();
		// ������ ������
		if (count($parameters[self::BY_SITE]) || count($parameters[self::BY_SITE_AND_LANG]))
			$sites = &self::_sites();
		
		// ���������� ����������, ����������� ��� ������� �����
		foreach($parameters[self::BY_LANG] as &$param)
			foreach($langs as &$lang)
				self::SaveParameterEntity($param['NAME'].$lang['LID_FORMATTED'], $param);
		
		// ���������� ����������, ����������� ��� ������� �����
		foreach($parameters[self::BY_SITE] as &$param)
			foreach($sites as &$site)
				self::SaveParameterEntity($param['NAME'], $param, $site['ID']);

		// ���������� ����������, ����������� ��� ������� ����� ������� �����
		foreach($parameters[self::BY_SITE_AND_LANG] as &$param)
			foreach($sites as &$site)
				foreach($langs as &$lang)
					self::SaveParameterEntity($param['NAME'].$lang['LID_FORMATTED'], $param, $site['ID']);

	}
	
	
	// �������-������� ��� SaveParameter
	// ������������ � SaveParametersType
	private function SaveParameterEntity($param_name, &$param, $site_id=false)
	{
		$val = $site_id===false? $_POST[$param_name]: $_POST[$param_name][$site_id];
	
		if (!self::_TextParameter($param) && self::ValidateParameter($param, $val))
			self::SaveParameter($param_name, $param, $val, $site_id);
	}
	
	
	// ������� ��������� ������������ �������� ���������� ��������� ���������� ����������
	private function ValidateParameter(&$param, $val)
	{
		return $param['VALIDATE']? preg_match($param['VALIDATE'], $val): true;		
	}
	
	/* ������� ��������� ��������� ��������
	 * ���������� ����� �� �������-������� SaveParameterEntity
	 * �� ������ ���� ��� /bitrix/modules/main/admin/settings.php __AdmSettingsSaveOption
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
	
	
	// ������� ������� ��������� �� ��, ���������� �� ��������� �����
	public function DeleteSiteParameters($site_id)
	{
		$parameters = &self::Parameters();
		foreach($parameters as &$parameters_by_type)
		{
			// �������� ������� ���������� �����
			foreach($parameters_by_type[self::BY_SITE] as &$param)
				COption::RemoveOption(self::MODULE_ID, $param['NAME'], $site_id);
			
			// �������� ���������� ����� ������� �����
			if (count($parameters_by_type[self::BY_SITE_AND_LANG]))
			{
				$langs = &self::_langs();
				foreach($langs as &$lang)
					foreach($parameters_by_type[self::BY_SITE_AND_LANG] as &$param)
						COption::RemoveOption(self::MODULE_ID, $param['NAME'].$lang['LID_FORMATTED'], $site_id);
			}
		}
	}
	
	// ������� ������� ��������� �� ��, ���������� �� ��������� �����
	public function DeleteLanguageParameters($language_id)
	{
		$lid_formatted = self::_lid_formatted($language_id);
		$parameters = &self::Parameters();

		foreach($parameters as &$parameters_by_type)
		{
			// �������� ������� ���������� �����
			foreach($parameters_by_type[self::BY_LANG] as &$param)
				COption::RemoveOption(self::MODULE_ID, $param['NAME'].$lid_formatted);
			
			// �������� ���������� ����� ������� �����
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
	
	// ������� ������� ��� ���������
	// ������������ ��� �������� ������
	public function DeleteAllParameters()
	{
		COption::RemoveOption(self::MODULE_ID);
	}
	
	
	// ��������������� �������, ���������� true, ���� �������� �������� ������� ��� ������ � ������� Common::DrawRow
	private function _TextParameter(&$param)
	{
		return !is_array($param) || isset($param['note']) || $param['TYPE']=="statictext" || $param['TYPE'] == "statichtml";
	}
	
	// ��������������� ������� ��� ������� GetParameters
	// ������� ������ � �������� ��������� ������
	private function _CheckErrors(&$parameters, &$type, &$show_empty_parameters_error)
	{
		foreach(array(self::NORMAL,self::BY_LANG,self::BY_SITE,self::BY_SITE_AND_LANG) as $params_type)
			$length[$params_type] = count($parameters[$params_type]);

		// �� ������� ���������
		if (!$length[self::NORMAL] && !$length[self::BY_LANG] && !$length[self::BY_SITE] && !$length[self::BY_SITE_AND_LANG])
		{
			if ($show_empty_parameters_error)
			{
				// �������� ����� "��� �����"
				$arOption = array('note' => self::_GetMessageByType('DEMTEAM_CURRENCYFORMAT_NO_OPTIONS', $type));
				return array(true, array($arOption));
			}
			else
			{
				return array(true);
			}			
		}
		
		
		// ������� ������ ��������� ��� ������� �����
		if (!$length[self::NORMAL] && !$length[self::BY_SITE] && ($length[self::BY_LANG] || $length[self::BY_SITE_AND_LANG]))
		{
			$langs = &self::_langs(); // ������ ������
			if (!count($langs))
			{
				// �������� ����� "��� ������"
				$arNoLanguagesOption = array('note' => self::_GetMessageByType('DEMTEAM_CURRENCYFORMAT_NO_LANGUAGES', $type));
			
				// ��� ������
				if ($length[self::BY_SITE_AND_LANG])
				{
					// ������� ��������� ��� ������� ����� ������� �����
					$items = array($arNoLanguagesOption);
					$sites = &self::_sites(); // ������ ������
					if (count($sites))
					{
						// ���� �����
						$subtabs = array();
						foreach($sites as &$site)
						{
							$subtabs[$site['ID']] = array('NAME' => $site['ID_FORMATTED'].$site['NAME'], 'ITEMS' => $items);
						}
						return array(true, array(array('SUBTABS'=>$subtabs)));
					}
					else
					{
						// �������� ����� "��� ������"
						$arNoSitesOption = array('note' => self::_GetMessageByType('DEMTEAM_CURRENCYFORMAT_NO_SITES', $type));
						// ��� ������
						return array(true, array($arNoSitesOption, $arNoLanguagesOption));
					}
				}
				else
				{
					// ������� ��������� ��� ������� �����
					return array(true, array($arNoLanguagesOption));
				}
			}
		}
	
		// ������� ������ ��������� ��� ������� ����� � ��� ������
		if (!$length[self::NORMAL] && !$length[self::BY_LANG] && ($length[self::BY_SITE] || $length[self::BY_SITE_AND_LANG]))
		{
			$sites = &self::_sites(); // ������ ������
			if (!count($sites))
			{
				// �������� ����� "��� ������"
				$arNoSitesOption = array('note' => self::_GetMessageByType('DEMTEAM_CURRENCYFORMAT_NO_SITES', $type));
				return array(true, array($arNoSitesOption));
			}
		}
		
		return array(false, array());
	}
	
	// ��������������� �������, ���������� �� ���� ��������������� ��������� �� ������� �����, �������� ��� � ���
	private function _GetMessageByType($name, &$type, $aReplace=false)
	{
		$s = GetMessage($name.'_'.$type, $aReplace);
		if ($s===NULL) $s = GetMessage($name, $aReplace);
		return $s;
	}
	
	// ��������������� �������, ���������� ������ ������
	private function &_langs()
	{
		static $langs;
		if (is_array($langs)) return $langs;
		
		$rsLangs = CLangAdmin::GetList($by = "sort", $order = "asc");
		while ($arRes = $rsLangs->GetNext())
			$langs[] = array('LID_FORMATTED'=>'_'.$arRes['LID'], 'NAME'=>$arRes['NAME']);
			
		return $langs;
	}
	
	// ��������������� �������, ����������� �� ����� ��� ����������� � �������� ���������
	// ������������, ����� ����� �������� � ����������� �� ���������� ����� (DeleteLanguageOptions)
	private function _lid_formatted($language_id)
	{
		return '_'.$language_id;
	}
	
	// ��������������� �������, ���������� ������ ������
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
