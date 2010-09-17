<?
require_once('mod.php');

class HiLight_Config
{
	var $fname;
	var $hash;
	var $delimeter;
	var $equation;

	function HiLight_Config($filename,$del=array('#','%~','[',']'),$eq='=')
	{
		if(empty($filename)||empty($del[0])||empty($del[1])||
				empty($eq)||!is_file($filename))return false;

		$this->fname=$filename;
		$this->delimeter=$del;
		$this->equation=$eq;
		$this->hash=array();
	}

	function &Read()
	{
		$_='';
		$fh=fopen($this->fname,'r');
		#flock($fh,4);
		while(fReadLine($fh,$_))
			if(strpos($_,$this->delimeter[0])===0)
			{
				if(count($arr=explode($this->equation,substr($_,1),2))!=2)continue;

				$arr[0]=trim($arr[0]);
				if(isSet($arr[1]))$arr[1]=trim($arr[1]);

				if(substr($arr[1],0,1)===$this->delimeter[2]&&
					substr($arr[1],-1)===$this->delimeter[3])
				{
					if(!isSet($this->hash[$arr[0]])||!is_array($this->hash[$arr[0]]))
												$this->hash[$arr[0]]=array();
					if(!isSet($this->hash[$arr[0]][$key=trim(substr($arr[1],1,-1))])||
						!is_array($this->hash[$arr[0]][$key]))
												$this->hash[$arr[0]][$key]=array();

					while(fReadLine($fh,$_)&&strlen($_))$this->hash[$arr[0]][$key][]=$_;
				}
				else
				{
					$arr[1]=explode($this->delimeter[1],$arr[1],2);

					if(!strlen($arr[1][0]=trim($arr[1][0]))||
						(($bool=isSet($arr[1][1]))&&!strlen($arr[1][1]=trim($arr[1][1]))))continue;

					if(isSet($this->hash[$arr[0]]))
					{
						if(is_array($this->hash[$arr[0]]))
						{
							if($bool)$this->hash[$arr[0]][$arr[1][0]]=$arr[1][1];
							else$this->hash[$arr[0]][]=$arr[1][0];
						}
					}
					elseif($bool)$this->hash[$arr[0]]=array($arr[1][0]=>$arr[1][1]);
					else $this->hash[$arr[0]]=$arr[1];
				}
			}
		#flock($fh,8);
		fclose($fh);

		$this->hash['CASE']=isSet($this->hash['CASE'])&&
							is_string($this->hash['CASE'])&&
							!CompareNoCase($this->hash['CASE'][0],'y');
		$this->hash['CMP']=$this->hash['CASE']?'CompareYesCase':'CompareNoCase';

		return $this->hash;
	}
}
?>