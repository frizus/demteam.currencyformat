<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @global CMain $APPLICATION */
/** @var string $price */
/** @var string $currency */
/** @var bool $useTemplate */
/** @var string $prologFile */
/** @var string $templateFolder */
/** @var string $site_id */
/** @var string $lang */
if (!function_exists('demteam_currencyformat_shorten_floordec'))
{
	function demteam_currencyformat_shorten_floordec($num, $decimals=0)
	{    
		return floor($num*pow(10,$decimals))/pow(10,$decimals);
	}
}

if (!function_exists('demteam_currencyformat_shorten'))
{
	function demteam_currencyformat_shorten($num, &$precision, $triads_min=0)
	{
		$triad_labels = GetMessage('DEMTEAM_CURRENCYFORMAT_SHORTEN_TRIADS');
		$triad_labels_max = count($triad_labels);
		
		$triads = floor(log($num, 1000));
		if ($triads>$triad_labels_max) $triads = $triad_labels_max;
		
		if ($triads>$triads_min)
		{
			$thousands = pow(1000, $triads);
			$exact = false;
			$numShorten = $num / $thousands;
			if ($precision>0)
				if ((int)$numShorten==(float)$numShorten)
				{
					$exact = true;
					$precision = 0;
				}
				else
				{
					$numShortenRounded = demteam_currencyformat_shorten_floordec($numShorten, $precision);
					$precision = strlen(strrchr($numShortenRounded, '.'))-1;
					
					$exact = $numShortenRounded==$numShorten;
					$numShorten = $numShortenRounded;
				}
			else
				$numShorten = floor($numShorten);
			
			return array($numShorten, @$triad_labels[$triads-1], $exact);
		}
		else
		{
			return array($num, false, true);
		}
	}
}