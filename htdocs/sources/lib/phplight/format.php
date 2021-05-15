<?
require_once('mod.php');

function func3(&$c,$m)
{
	$c.=$m;
	return'';
}

class HiLight_Format
{
	var $cfg;

	function __construct(&$config)
	{
		$this->cfg=$config;
	}

	function addStr(&$nstr,$format,$arr)
	{
		$res=preg_replace('/\%\{(\d+)\}/',
						(isSet($arr[$k=intVal('\1')])?
						#$arr[$k]
						$arr[$k]
						:''),$format);
		return $nstr.=$res;
	}

	function func1(&$arr,$sch,$c,&$cfg)
	{
		$arr=array();
		foreach($sch as $k=>$v)if($cfg->hash['CMP'](mb_substr($v,0,mb_strlen($c)),$c))$arr[]=$v;
		return count($arr);
	}

	function func2(&$arr,$cstr,&$cfg)
	{
		foreach($arr as $k=>$v)if(!$cfg->hash['CMP']($v,$cstr))unSet($arr[$k]);
		return count($arr=array_values($arr));
	}

	function func4(&$str,&$cstr,$format)
	{
		$str=preg_replace('/'.$format.'/se',"func3(\$cstr,'\\1')",$str);
	}

	function func5(&$arr,$c,&$con,&$coff,&$cfg)
	{
		$l='';
		$arr=array();
		if(is_array($con)) foreach($con as $k=>$v)if($cfg->hash['CMP']($c,$v[0])) $arr[]=array($v,$coff[$k]);
		return count($arr);
	}

	function func6(&$arr,$sch,$c,&$cfg)
	{
		$arr=array();
		foreach($sch as $k=>$v)if($cfg->hash['CMP'](mb_substr($v[0],0,mb_strlen($c)),$c))$arr[]=$v;
		return count($arr);
	}

	function Begin(&$str)
	{
		$nstr=$cstr=$c='';
		$res=false;
		$flag=array(false,false);

		while(strGetC($str,$c))
		{
			if(!(mb_strpos($this->cfg->hash['CHARS'][0],$c)===false))
			{
				$cstr.=$c;
				continue;
			}
			else
			{
				if(mb_strlen($cstr))
				{
					$res=false;
					foreach($this->cfg->hash['KEYWORD'] as $k1=>$v1)
					{
						foreach($v1 as $k2=>$v2)
						{
							if($this->cfg->hash['CMP']($cstr,$v2))
							{
								$this->addStr($nstr,$this->cfg->hash['STYLE'][$k1],array($cstr));
								$res=true;
								break;
							}
						}
						if($res)break;
					}
				}

				if(!$res)$this->addStr($nstr,
						$this->cfg->hash['STYLE'][is_numeric($cstr)?'Digits':'Text'],array($cstr));
			}
			$cstr='';

			if(in_array($c,$this->cfg->hash['QUOTATION']))
			{
				$l=$c;
				$cstr=$c;

				while(mb_strlen($cstr)&&strGetC($str,$c))
				{
					$cstr.=$c;
					if(!$this->cfg->hash['CMP']($c,$l))continue;

					$this->addStr($nstr,$this->cfg->hash['STYLE']['Quotation'],array($cstr));
					$cstr='';
				}

				$nstr.=$cstr;
				$cstr='';
			}
			elseif(!$flag[0]&&$this->func5($arr,$c,
								$this->cfg->hash['COMMENTON'],
								$this->cfg->hash['COMMENTOFF'],
								$this->cfg))
			{
				$cstr.=$c;
				while(true)
				{
					$all=count($arr);
					if($all==1&&$this->cfg->hash['CMP']($arr[0][0],$cstr))
					{
						$this->func4($str,$cstr,'^(.*?'.quoteAll($arr[0][1]).')');

						$this->addStr($nstr,$this->cfg->hash['STYLE']['Comment'],array($cstr));
						break;
					}
					elseif($all&&strGetC($str,$c))
					{
						$cstr.=$c;
						$this->func6($arr,$arr,$cstr,$this->cfg);
					}
					else
					{
						$str=$cstr.$str;
						$flag[0]=true;
						break;
					}
				}

				$cc=$cstr='';
				if($flag[0])continue;
			}
			elseif(!$flag[1]&&$this->func1($arr,$this->cfg->hash['LINECOMMENT'],$c,$this->cfg))
			{
				$cstr.=$c;

				while(true)
				{
					$all=count($arr);
					if($all==1&&$this->cfg->hash['CMP']($arr[0],$cstr))
					{
						$pos=intVal(mb_strpos($str,"\n"));
						$cstr=$c.$c.mb_substr($str,0,$pos+1);
						$str=mb_substr($str,$pos+1);

						$this->addStr($nstr,$this->cfg->hash['STYLE']['Comment'],array($cstr));
						$cstr='';
						break;
					}
					elseif($all&&strGetC($str,$c))
					{
						$cstr.=$c;
						$this->func2($arr,$cstr,$this->cfg);
					}
					else
					{
						$str=$cstr.$str;
						$flag[1]=1;
						break;
					}
				}
				$c=$cstr='';
				if($flag[1])continue;
			}
			else
			{
				$l=false;
				foreach(array('PREFIX','DELIMITER') as $k=>$v)
				{
					$bool=false;
					if(isSet($this->cfg->hash[$v])&&is_array($this->cfg->hash[$v]))
					foreach($this->cfg->hash[$v] as $k1=>$v1)
					{
						if($this->cfg->hash['CMP']($c,$v1))
						{
							$l=$bool=true;
							break;
						}
					}

					if(!$bool)continue;

					if(($key=ucfirst(mb_strtolower($v)))==='Prefix')
					{
						$this->addStr($nstr,$this->cfg->hash['STYLE'][$key.'1'],array($c));
						$c='';

						$str=preg_replace('/^(['.quotemeta($this->cfg->hash['CHARS'][0]).']+)/e',
																		'func3($c,'."'\\1'".')',$str);
						if(!mb_strlen($c))continue;

						$key.='2';
					}

					$this->addStr($nstr,$this->cfg->hash['STYLE'][$key],array($c));
					break;
				}

				if(!$l)$this->addStr($nstr,
								$this->cfg->hash['STYLE'][is_numeric($cstr)?'Digits':'Text'],array($c));
			}
			$flag=array(false,false);
		}

		return $nstr;
	}
}
?>
