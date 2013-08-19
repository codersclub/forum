<?php
//-----------------------------------------------------------------
// MIME Types Magic Crutcher
// Allow to upload archves with "application/octetstream" mime type
// (c) Barazuk at sources.ru 15.07.2007
//-----------------------------------------------------------------

function mime_crutch($filename)
{
	$return_type = "application/octetstream";

	$handle = fopen($filename, "rb");
	if (!$handle)
	{
		//  echo("File not found!\n");
		// А что тут, собственно, делать при ошибке открытия - не знаю. Пустую строку вернуть?
		return "";
	}

	$content = fread($handle, 16);

	for (; ;)
	{

		// ----- First, check files with simple signatures at beginning

		// Archives
		// -------------------------------------------------------------
		// First, 100% correctly detection (signatures was embedded in
		// format from beginning of development and always present)
		// Partially from magic.mime, partially from developers site (7zip)
		if (!strncmp($content, "PK\x03\x04", 4))
		{
			$return_type = "application/zip";
			break;
		}

		if (!strncmp($content, "Rar!", 4))
		{
			$return_type = "application/x-rar-compressed";
			break;
		}

		if (!strncmp($content, "7z\xbc\xaf", 4))
		{
			$return_type = "application/x-7z-compressed";
			break;
		}

		// Second, UNIX archives.
		//from magic.mime
		if (!strncmp($content, "BZh", 3))
		{
			$return_type = "application/x-bzip2";
			break;
		}

		//from magic.mime
		if (!strncmp($content, "\x1F\x8B", 2))
		{
			$return_type = "application/x-gzip";
			break;
		}

		// From sources of Multiarc (FAR plugin)
		if (!strncmp($content, "\x1F\x9D", 2))
		{
			$return_type = "application/x-compress";
			break;
		}

		// Don't know signatures
		//    "application/mac-binhex40"  => array( 1, 'stuffit.gif'   , 'Mac Binary'     ) ,

		// Documents
		// -------------------------------------------------------------
		if (!strncmp($content, "%PDF-", 5))
		{
			$return_type = "application/pdf";
			break;
		}

		// ----- Second, check files with simple less reliable format detection

		// tar - compilation from magic.mime & sources of Multiarc (FAR plugin)

		// Check file length first
		if (filesize($filename) > 500 && !fseek($handle, 0))
		{
			// Read TAR posix_header
			$content2 = fread($handle, 500);

			// test #1: test signatures
			// ------------------------

			//    "application/x-gtar" => array( 1, 'zip.gif'       , 'GZipped TAR Ball') ,
			// 257   string      ustar\040\040\0   application/x-gtar
			if (!strncmp(substr($content2, 257), "ustar  \0", 8))
			{
				$return_type = "application/x-gtar";
				break;
			}

			//    "application/x-tar"     => array( 1, 'zip.gif'       , 'TAR Ball'       ) ,
			// 257   string      ustar\0     application/x-tar
			if (!strncmp(substr($content2, 257), "ustar\0", 6))
			{
				$return_type = "application/x-tar";
				break;
			}

			// test #2: validate signatureless header for correctness
			// ------------------------

			// subtest #1: name must not contain chars less than ' '
			//  for (I=0;Header->name[I];I++)
			//    if (I==sizeof(Header->name) || Header->name[I] < ' ')
			//  return(FALSE);

			$subtest1 = true;
			for ($i = 0; $i < 100; $i++)
			{
				if ($content2[$i] < ' ')
				{
					$subtest1 = false;
					break;
				}
			}

			// subtest #2: test mode
			// for (I=0;I < 8;I++)
			//   if (Header->mode[I] > '7' || Header->mode[I]<'0' && Header->mode[I]!=0 && Header->mode[I]!=' ')
			//     return(FALSE);
			//   }

			$subtest2 = true;
			for ($i = 100; $i < 108; $i++)
			{
				if ($content2[$i] > '7' || ($content2[$i] < '0' && $content2[$i] != 0 && $content2[$i] != ' '))
				{
					$subtest2 = false;
					break;
				}
			}

			// Subtest 3: I don't like it - less informative
			//  if(strcmp(Header->name,"././@LongLink"))
			//  {
			//    DWORD Seconds=GetOctal(Header->mtime);
			//    if (Seconds<300000000 || Seconds>1500000000)
			//      return(FALSE);
			//  }

			if ($subtest1 && $subtest2)
			{
				$return_type = "application/x-tar";
				break;
			}
		}

		break;
	}

	fclose($handle);

	return $return_type;
}

function mime_type_by_content($filename)
{
	if (function_exists('mime_content_type'))
	{
		return mime_content_type($filename);
	}
	return false;
}

function mime_type_fileinfo($filename)
{
	if (extension_loaded('fileinfo'))
	{
		if (defined('FILEINFO_MIME_TYPE'))
		{ // as of 5.3
			$res = finfo_open(FILEINFO_MIME_TYPE);
		} else
		{
			$res = finfo_open(FILEINFO_MIME);
		}
		$detected_mime2 = finfo_file($res, $filename);
		finfo_close($res);
		return $detected_mime2;
	}
	return false;
}

function mime_type_file_util($filename)
{
	global $INFO, $ibforums;
	if (isset($INFO) && isset($INFO['file_util_command']))
	{
		// на случай, когда работаем со страницы mime.php
		$file_util_command = $INFO['file_util_command'];
	} elseif (isset($ibforums) && isset($ibforums->vars['file_util_command']))
	{
		// во всех остальных случаях
		$file_util_command = $ibforums->vars['file_util_command'];
	} else
	{
		return false;
	}
	$filename = escapeshellarg($filename);
	if ($type = exec("{$INFO['file_util_command']} $filename"))
	{
		return trim($type);
	}
	return false;
}

function detect_mime_type($filename, $try_all_methods = false)
{
	static $methods = array(
		'mime_type_fileinfo', // перавя, т.к. рекомендуемая
		'mime_type_file_util',
		'mime_type_by_content', // не вторая, т.к. устаревшая
		'mime_crutch' // последняя, ибо кастыль
	);
	$checks = array();
	foreach ($methods as $func)
	{
		$type = $func($filename);
		if ($type && !$try_all_methods)
		{
			return $type;
		}
		$checks[$func] = $type;
	}
	if ($try_all_methods)
	{
		return $checks;
	} else
	{
		return false;
	}
}

function mime_type_is_allowed($type)
{
	require "./conf_mime_types.php";
	return $mime_types[$type][0] == 1;
}


