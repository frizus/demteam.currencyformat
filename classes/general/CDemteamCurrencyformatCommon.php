<?
IncludeModuleLangFile(__FILE__);
class CDemteamCurrencyformatCommon
{
	const MODULE_ID = demteam_currencyformat::MODULE_ID;
	const PREFIX = demteam_currencyformat::PREFIX;
	
	// Функция возвращает результат после включения указанного файла
	public function return_output($filepath)
	{
		if (file_exists($filepath))
		{
			ob_start();
			include $filepath;
			return ob_get_clean();
		}
	}
	
	/* Функция гененирирует субтабы и заполняет их параметрами
	 * Вид массива описан методе GetParameters класса параметров
	 */
	public function GenerateSubtabs(&$arParameter)
	{
		$aTabs = array();
		foreach($arParameter['SUBTABS'] as $site_id=>&$arSubtab)
		{
			$aTabs[] = array(
				'DIV' => self::PREFIX.'subedit'.$site_id,
				'TAB' => $arSubtab['NAME'],
				//'TITLE' => $arSubtab['NAME'],
			);
		}
		
		static $i; $i++;
		// Вывод вложенных вкладок -- НАЧАЛО
		$tabControl = new CAdminViewTabControl(self::PREFIX.'ViewTabControl'.$i, $aTabs);
		echo '<tr><td colspan="2">';
		$tabControl->Begin();

		foreach($arParameter['SUBTABS'] as &$arSubtab)
		{
			$tabControl->BeginNextTab();
			echo '<table class="adm-detail-content-table edit-table">';
			self::DrawList($arSubtab['ITEMS']);
			echo '</table>';
		}

		$tabControl->End();
		echo '</td></tr>';
		// Вывод вложенных вкладок -- КОНЕЦ
	}
	
	/* Функция выводит табличную строку с параметром
	 * За основу взят код /bitrix/modules/main/admin/settings.php __AdmSettingsDrawRow
	 */
	public function DrawRow(&$param)
	{
		if(!is_array($param)):
		?>
			<tr class="heading">
				<td colspan="2"><?=$param?></td>
			</tr>
		<?
		elseif(isset($param["note"])):
		?>
			<tr>
				<td colspan="2" align="center">
					<?echo BeginNote('align="center"');?>
					<?=$param["note"]?>
					<?echo EndNote();?>
				</td>
			</tr>
		<?
		else:
			$name = $param['INPUT_NAME'];
			$label = $param['LABEL'];
			$val = $param['VALUE'];
			$type = $param['TYPE'];
			$type_add1 = $param['TYPE_ADD1'];
			$type_add2 = $param['TYPE_ADD2'];
			$disabled = $param['DISABLED']=='Y' ? ' disabled' : '';
			$sup_text = strlen($param['SUP_TEXT']) ? $param['SUP_TEXT'] : '';
		?>
			<tr>
				<td<?if($type=="multiselectbox" || $type=="textarea" || $type=="statictext" || $type=="statichtml") echo ' class="adm-detail-valign-top"'?> width="50%"><?
					if($type=="checkbox")
						echo "<label for='".htmlspecialcharsbx($name)."'>".$label."</label>";
					else
						echo $label;
					if (strlen($sup_text) > 0)
					{
						?><span class="required"><sup><?=$sup_text?></sup></span><?
					}
						?></td>
				<td width="50%"><?
				if($type=="checkbox"):
					?><input type="checkbox" id="<?echo htmlspecialcharsbx($name)?>" name="<?echo htmlspecialcharsbx($name)?>" value="Y"<?if($val=="Y")echo" checked";?><?=$disabled?><?if($type_add2<>'') echo " ".$type_add2?>><?
				elseif($type=="text" || $type=="password"):
					?><input type="<?echo $type?>" size="<?echo $type_add1?>" maxlength="255" value="<?echo htmlspecialcharsbx($val)?>" name="<?echo htmlspecialcharsbx($name)?>"<?=$disabled?><?=($type=="password"? ' autocomplete="off"':'')?>><?
				elseif($type=="selectbox"):
					$arr = $type_add1;
					if(!is_array($arr))
						$arr = array();
						
					$arr_keys = array_keys($arr);
					?><select name="<?echo htmlspecialcharsbx($name)?>" <?=$disabled?>><?
						if($type_add2!='N'):?><option><?=GetMessage('DEMTEAM_CURRENCYFORMAT_DEFAULT_SELECT')?></option><?endif;
						for($j=0; $j<count($arr_keys); $j++):
							?><option value="<?echo $arr_keys[$j]?>"<?if($val==$arr_keys[$j])echo" selected"?>><?echo htmlspecialcharsbx($arr[$arr_keys[$j]])?></option><?
						endfor;
						?></select><?
				elseif($type=="multiselectbox"):
					$arr = $type_add1;
					if(!is_array($arr))
						$arr = array();
					$arr_keys = array_keys($arr);
					$arr_val = explode(",",$val);
					?><select size="5" multiple name="<?echo htmlspecialcharsbx($name)?>[]"<?=$disabled?>><?
						for($j=0; $j<count($arr_keys); $j++):
							?><option value="<?echo $arr_keys[$j]?>"<?if(in_array($arr_keys[$j],$arr_val)) echo " selected"?>><?echo htmlspecialcharsbx($arr[$arr_keys[$j]])?></option><?
						endfor;
					?></select><?
				elseif($type=="textarea"):
					?><textarea rows="<?echo $type_add1?>" cols="<?echo $type_add2?>" name="<?echo htmlspecialcharsbx($name)?>"<?=$disabled?>><?echo htmlspecialcharsbx($val)?></textarea><?
				elseif($type=="statictext"):
					echo htmlspecialcharsbx($val);
				elseif($type=="statichtml"):
					echo $val;
				endif;
				?></td>
			</tr>
		<?
		endif;
	}
	
	/* Функция выводит список параметров табличными строками
	 * За основу взят код /bitrix/modules/main/admin/settings.php __AdmSettingsDrawList
	 */
	public function DrawList(&$parameters)
	{
		foreach($parameters as &$param)
		{
			self::DrawRow($param);
		}
	}
	
}
?>