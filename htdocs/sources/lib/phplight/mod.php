<?
function fReadLine(&$fh,&$str)
{
	$str='';
	while(!feof($fh)&&($c=fgetc($fh))!="\n")if($c!="\r")$str.=$c;
	$str=trim($str);

	return !empty($c);
}

function quoteAll($str)
{
	$nstr='';
	while(mb_strlen($str))
	{
		if(mb_strpos('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_',$str[0])===false)
			$nstr.="\\".$str[0];
		else $nstr.=$str[0];

		$str=mb_substr($str,1);
	}
	return$nstr;
}

function strGetC(&$str,&$c)
{
	$c=$str[0];
	$str=mb_substr($str,1);
	return mb_strlen($c);
}

function toUpperCase($str)
{
	return strtr(mb_strtoupper($str),'абвгдеёжзийклмнопрстуфхцчшщъыьэюя',
									'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ');
}

function CompareNoCase($a,$b)
{
	return !strcasecmp($a,$b)||toUpperCase($a)===toUpperCase($b);
}

function CompareYesCase($a,$b)
{
	return $a===$b;
}
?>
