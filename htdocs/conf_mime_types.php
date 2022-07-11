<?php

$mime_types = array(
  "text/plain"	   => array( 1, 'text.gif'       , 'Text Document'  ),

// Applications
  "application/pdf"	   => array( 1, 'pdf.gif'       , 'PDF Document'  ),
  "application/msword" 	   => array( 1, 'word.gif'      , 'MS Word doc'   ),
  "application/powerpoint" => array( 1, 'apps.gif'      , 'PowerPoint Doc'),
  "application/postscript" => array( 1, 'postscript.gif', 'Postscript'    ),

// Images
  "image/x-png" 	=> array( 1, 'quicktime.gif' , 'PNG Image'  ,1  ),
  "image/png" 		=> array( 1, 'quicktime.gif' , 'PNG Image'  ,1  ),
  "image/gif" 		=> array( 1, 'gif.gif'       , 'GIF Image'  ,1  ),
  "image/ico"		=> array( 1, 'gif.gif'       , 'Icon File'      ),
  "image/icon"		=> array( 1, 'gif.gif'       , 'Icon File'      ),
  "image/x-icon"	=> array( 1, 'gif.gif'       , 'Icon File'      ),
  "image/x-MS-bmp"	=> array( 1, 'bmp.gif'       , 'BMP Image'      ),
  "image/tiff" 		=> array( 1, 'quicktime.gif' , 'TIFF Image'     ),
  "image/jpeg" 		=> array( 1, 'jpeg.gif'      , 'JPEG Image' ,1  ),
  "image/pjpeg" 	=> array( 1, 'jpeg.gif'      , 'JPEG Image' ,1  ),
  "image/vnd.djvu" 	=> array( 1, 'djvu.gif'      , 'DJVU file'  ,1  ),

// Multimedia
  "video/quicktime" 		=> array( 1, 'quicktime.gif' , 'QuickTime Movie'),
  "video/vivo"			=> array( 1, 'win_player.gif', 'VIVO Movie'     ),
  "video/mpeg"			=> array( 1, 'quicktime.gif' , 'MPEG Video'     ),
  "video/x-msvideo" 		=> array( 1, 'win_player.gif', 'MS Video'       ),
//  "audio/x-pn-realaudio"	=> array( 1, 'sound.gif'     , 'Real Media (Audio / Video)'),
  "audio/x-pn-realaudio"	=> array( 1, 'real_audio.gif', 'Real Audio File'),
  "audio/x-realaudio"		=> array( 1, 'realaudio.gif' , 'Real Audio'     ),
  "audio/x-wav" 		=> array( 1, 'sound.gif'     , 'WAV File'       ),
  "audio/midi" 			=> array( 1, 'sound.gif'     , 'MIDI File'      ),
  "audio/x-mpeg"		=> array( 1, 'mp3.gif'       , 'MPEG Audio'     ),
  "audio/mpeg" 			=> array( 1, 'mp3.gif'       , 'MPEG Audio'     ),
  "audio/x-aiff" 		=> array( 1, 'quicktime.gif' , 'AIFF File'      ),
  "application/octet-stream" 	=> array( 0, 'quicktime.gif' , 'OCTET Stream'   ),

// Archives
  "application/x-compress" 	 => array( 1, 'zip.gif'    , 'Compressed File'),
  "application/x-tar"		 => array( 1, 'zip.gif'    , 'TAR Ball'       ),
  "application/x-gtar"		 => array( 1, 'zip.gif'    , 'GZipped TAR Ball'),
  "application/mac-binhex40"     => array( 1, 'stuffit.gif', 'Mac Binary'     ),
  "application/zip" 		 => array( 1, 'zip.gif'    , 'ZIP File'       ),
  "application/x-zip" 		 => array( 1, 'zip.gif'    , 'ZIP File'       ),
  "application/x-zip-compressed" => array( 1, 'zip.gif'    , 'ZIP File'       ),
  "application/x-gzip"	 	 => array( 1, 'zip.gif'    , 'GZIP File'      ),
  "application/x-gzip-compressed"=> array( 1, 'zip.gif'    , 'GZIP File'      ),
  "application/x-bzip2" 	 => array( 1, 'zip.gif'    , 'BZIP2 File'     ),
  "application/x-7z-compressed"  => array( 1, 'zip.gif'    , '7zip Archive'   ),
  "application/x-rar"		 => array( 1, 'rar.gif'    , 'RAR File'       ),
  "application/x-rar-compressed" => array( 1, 'rar.gif'    , 'RAR File'       ),
// patch
  'text/x-diff'				=> array( 1, 'text.gif'    , 'Patch File'       ),
);

