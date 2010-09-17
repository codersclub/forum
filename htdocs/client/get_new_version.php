<?

$new_version = "1.212";

if(1 == $check_only) {

	echo $new_version;
	exit();
}

if(empty($version)) {
	
	$version = $new_version;
}

$files = array(
	"1.209" => "Forumizer_sources_ru_1.209.zip"
);

if(empty($files[$version])) {
	
	echo "MESSAGE: NO SUCH VERSION!";
	exit();
}

$new_version_url = "" . $files[$version];

$size = (string)(filesize( $new_version_url ));

$header =	"Content-Type: application/zip;\n" .
		"Content-Disposition: inline;\n" .
		"filename=\"" . $files[$version] . "\";\n" .
		"Content-Length: " . $size . ";";

@header( $header );

$fh = fopen( $new_version_url, 'rb' );
fpassthru( $fh );
@fclose( $fh );


?>