<HTML>
<HEAD>
<TITLE>Check for Upload MIME type</TITLE>
</HEAD>
<BODY>


<?php

$enable_log=false;

require "./sources/mimecrutch.php";
//<- Added by Barazuk 21/07/07
require "./conf_mime_types.php";
//-> Added by Barazuk 21/07/07
require "../conf_global.php";


if(count($_FILES)){

  //$headers = apache_request_headers();
  //foreach ($headers as $header => $value) {
  //   echo "$header: $value <br>\n";
  //}

	
  echo "Uploaded file parameters:<br><br>\n";
  echo "Useragent: ".$_SERVER['HTTP_USER_AGENT']."<br>\n";
  echo "<HR>\n";

  foreach ($_FILES as $header => $value) {

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
        /*
        if(extension_loaded('mime_magic')) {
          $detected_mime=mime_content_type($value['tmp_name']);
          echo "Detected MIME: $detected_mime <br>\n";
        }

        if(extension_loaded('fileinfo')) {
          $res = finfo_open(FILEINFO_MIME); 
          $detected_mime2 = finfo_file($res, $value['tmp_name']);
          echo "Fileinfo: $detected_mime2 \n";
          finfo_close($res);
        }

        //<- Added by Barazuk 21/07/07
        //-> Added by Barazuk 21/07/07

        $detected_mime_crutch = mime_crutch($value['tmp_name']);

        //<- Added by Barazuk 21/07/07
        $allowed = $mime_types[$detected_mime_crutch][0] == 1 ?
          "<span style='color:green'><B>allowed</B></span>" :
          "<span style='color:red'><B>deprecated</B></span>" ;
        //-> Added by Barazuk 21/07/07

        //<- Modified by Barazuk 21/07/07
        echo "MIME Crutch: $detected_mime_crutch ($allowed)\n";
        */
        
        $allowed = $mime_types[$value['type']][0] == 1 ?
          "<span style='color:green'><B>allowed</B></span>" :
          "<span style='color:red'><B>deprecated</B></span>" ;
        echo "MIME type ".$value['type']." is $allowed<BR>\n";
        
        $type_titles = array(
			'mime_type_fileinfo' => 'Fileinfo', 
			'mime_type_file_util' => 'file', 
			'mime_type_by_content' => 'Detected MIME',
			'mime_crutch' => 'MIME Crutch'
        );
        foreach (detect_mime_type($value['tmp_name'], true) as $check_type => $file_type) {
        	
	        $allowed = 
	        	mime_type_is_allowed($file_type) 
	        	?
	          		"<span style='color:green'><B>allowed</B></span>"
	          	:
	          		"<span style='color:red'><B>failed</B></span>" ;
	        
	        echo "{$type_titles[$check_type]}: {$file_type} is $allowed<BR>\n";
	        
        }
        //-> Modified by Barazuk 21/07/07
      } //if($value['error'] == 0){

      echo "<HR>\n";
    } //if(header[name])

  }     // foreach ($HTTP_POST_FILES as $header => $value) {


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