<HTML>
<HEAD>
<TITLE>Check for Upload MIME type</TITLE>
</HEAD>
<BODY>


<?php

$enable_log=false;

function mime_crutch($filename)
{
  $return_type = "application/octetstream";

  $handle = fopen($filename, "rb");
  if(!$handle){
//  echo("File not found!\n"); 
// А что тут, собственно, делать при ошибке открытия - не знаю. Пустую строку вернуть?
    return "";
  }

  $content = fread($handle, 16);

  for(;;){

  // ----- First, check files with simple signatures at beginning

  // Archives
  // -------------------------------------------------------------
    // First, 100% correctly detection (signatures was embedded in 
    // format from beginning of development and always present)
    // Partially from magic.mime, partially from developers site (7zip)
    if(!strncmp($content, "PK\x03\x04", 4)){ 
      $return_type = "application/zip";
      break;
    }

    if(!strncmp($content, "Rar!", 4)){ 
      $return_type = "application/x-rar-compressed";
      break;
    }

    if(!strncmp($content, "7z\xbc\xaf", 4)){ 
      $return_type = "application/x-7z-compressed";
      break;
    }

    // Second, UNIX archives. 
    //from magic.mime
    if(!strncmp($content, "BZh", 3)){
      $return_type = "application/x-bzip2";
      break;
    }

    //from magic.mime
    if(!strncmp($content, "\x1F\x8B", 2)){ 
      $return_type = "application/x-gzip";
      break;
    }

    // From sources of Multiarc (FAR plugin)
    if(!strncmp($content, "\x1F\x9D", 2)){ 
      $return_type = "application/x-compress";
      break;
    }

    // Don't know signatures
//    "application/mac-binhex40"  => array( 1, 'stuffit.gif'   , 'Mac Binary'     ) ,

  // Documents
  // -------------------------------------------------------------
    if(!strncmp($content, "%PDF-", 5)){ 
      $return_type = "application/pdf";
      break;
    }



  // ----- Second, check files with simple less reliable format detection

    // tar - compilation from magic.mime & sources of Multiarc (FAR plugin)

    // Check file length first 
    if(filesize($filename) > 500 && !fseek($handle, 0)){
      // Read TAR posix_header
      $content2 = fread($handle, 500);

      // test #1: test signatures
      // ------------------------

      //    "application/x-gtar" => array( 1, 'zip.gif'       , 'GZipped TAR Ball') ,
      // 257   string      ustar\040\040\0   application/x-gtar
      if(!strncmp(substr($content2, 257), "ustar  \0", 8)){
        $return_type = "application/x-gtar";
        break;
      }

      //    "application/x-tar"     => array( 1, 'zip.gif'       , 'TAR Ball'       ) ,
      // 257   string      ustar\0     application/x-tar
      if(!strncmp(substr($content2, 257), "ustar\0", 6)){
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
      for($i = 0; $i < 100; $i++)
        if($content2[$i] < ' '){ 
          $subtest1 = false;
          break;
        }

      // subtest #2: test mode
      // for (I=0;I < 8;I++)
      //   if (Header->mode[I] > '7' || Header->mode[I]<'0' && Header->mode[I]!=0 && Header->mode[I]!=' ')
      //     return(FALSE);
      //   } 

      $subtest2 = true;
      for($i = 100; $i < 108; $i++)
        if($content2[$i] > '7' || ($content2[$i] < '0' && $content2[$i] != 0 && $content2[$i] != ' ')){ 
          $subtest2 = false;
          break;
        }

      // Subtest 3: I don't like it - less informative
      //  if(strcmp(Header->name,"././@LongLink"))
      //  {
      //    DWORD Seconds=GetOctal(Header->mtime);
      //    if (Seconds<300000000 || Seconds>1500000000)
      //      return(FALSE);
      //  }


      if($subtest1 && $subtest2){
        $return_type = "application/x-tar";
        break;
      }
    }


    break;
  }

  fclose($handle);

  return $return_type;
}


if(count($HTTP_POST_FILES)){

  //$headers = apache_request_headers();
  //foreach ($headers as $header => $value) {
  //   echo "$header: $value <br>\n";
  //}


  echo "Uploaded file parameters:<br><br>\n";
  echo "Useragent: ".$_SERVER['HTTP_USER_AGENT']."<br>\n";
  echo "<HR>\n";

  foreach ($HTTP_POST_FILES as $header => $value) {

    if($value['name'])
    {
//      echo "header[name]:".$value['name']."<br>\n";
//      echo "header[size]:".$value['size']."<br>\n";

      echo "<b>$header</b>:<br>\n";


      foreach ($value as $key => $val) {
        echo "  $key: $val <br>\n";
      }

      if($value['error'] == 0){
        echo "Filename: {$value['tmp_name']}<br>\n";

        if(extension_loaded('mime_magic')) {
          $detected_mime=mime_content_type($value['tmp_name']);
          echo "Detected MIME: $detected_mime <br>\n";
        }

        if(extension_loaded('fileinfo')) {
          $res = finfo_open(FILEINFO_MIME); /* return mime type ala mimetype extension */
          $detected_mime2 = finfo_file($res, $value['tmp_name']);
          echo "Fileinfo: $detected_mime2 \n";
          finfo_close($res);
        }

        $detected_mime_crutch = mime_crutch($value['tmp_name']);
        echo "MIME Crutch: $detected_mime_crutch\n";
      }	//if($value['error'] == 0){

      echo "<HR>\n";
    } //if(header[name])

  }	// foreach ($HTTP_POST_FILES as $header => $value) {


  if ($enable_log && $FH = @fopen( 'mime.log', 'a' ) )
  {
    $path_parts = pathinfo($HTTP_POST_FILES['file1']['name']);

    @fwrite( $FH, date("d.m.Y h:i:s ")."Useragent: ".$_SERVER['HTTP_USER_AGENT']."\n");
    @fwrite( $FH, date("d.m.Y h:i:s ")."File: ".$HTTP_POST_FILES['file1']['name']."\n");
    @fwrite( $FH, date("d.m.Y h:i:s ")."Ext: ".$path_parts['extension']."\n");
    @fwrite( $FH, date("d.m.Y h:i:s ")."Type: ".$HTTP_POST_FILES['file1']['type']."\n");
    @fwrite( $FH, "\n");
    @fclose($FH);
  }

//$filename=$HTTP_POST_FILES['file1']['tmp_name'];
//$filename=str_replace("\\",'/',$filename);
//if(unlink($filename)) {
//  echo "File ".$filename." removed.<br>";
//};
//  echo "<HR>";
}

?> 




<h2>Check for Upload MIME type</h2>
<!--FORM METHOD="POST" ACTION=" <?php echo $_SERVER['PHP_SELF'] ?>" ENCTYPE="multipart/form-data"-->
<FORM METHOD="POST" ENCTYPE="multipart/form-data">
Upload files:<br>
File 1: <INPUT TYPE="file" NAME="file1">
<br>
File 2: <INPUT TYPE="file" NAME="file2">
<br>
File 3: <INPUT TYPE="file" NAME="file3">
<br>

<INPUT TYPE="SUBMIT" VALUE="Start Upload">

</FORM>

</BODY>
</HTML>