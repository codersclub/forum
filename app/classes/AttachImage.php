<?php
// use finfo;

class AttachImage extends Attachment
{

	public function isImage()
	{
		return true;
	}

	public function getPeviewLink()
	{
		global $ibforums;

		$path = $this->previewPath();
		//
		if (!file_exists($path))
		{
			if ($this->mkPreview())
			{
				return $this->previewUrl();
			} else
			{
				return $this->realImageUrl();
			}
		}
		return $this->previewUrl();

	}

	private function mkPreview()
	{
		global $ibforums, $std;
		$img = $this->loadImage();
		if (!$img)
		{
			return false;
		}
		$w_src = imagesx($img);
		$h_src = imagesy($img);

		$im      = $std->scale_image(array(
		                                  'max_width'  => $ibforums->vars['siu_width'],
		                                  'max_height' => $ibforums->vars['siu_height'],
		                                  'cur_width'  => $w_src,
		                                  'cur_height' => $h_src
		                             ));
		$ratio_w = $w_src / $im['img_width'];
		$ratio_h = $h_src / $im['img_height'];

		$w_dest = round($w_src / $ratio_w);
		$h_dest = round($h_src / $ratio_h);

		$dest = imagecreatetruecolor($w_dest, $h_dest);

		if (mb_substr($this->realFilename(), -4) == '.png')
		{
			imagealphablending($dest, false);
			imagesavealpha($dest, true);
		}

		imagecopyresampled($dest, $img, 0, 0, 0, 0, $w_dest, $h_dest, $w_src, $h_src);

		return $this->saveImage($dest);
	}

	function getPreviewSizes()
	{
		global $ibforums, $std;
		$img_size = @GetImageSize($this->previewPath());

		if ($img_size)
		{

			return array(
				'img_width'  => $img_size[0],
				'img_height' => $img_size[1]
			);
		} else
		{
			$img_size = $this->getRealSize();

			$im = $std->scale_image(array(
			                             'max_width'  => $ibforums->vars['siu_width'],
			                             'max_height' => $ibforums->vars['siu_height'],
			                             'cur_width'  => $img_size['width'],
			                             'cur_height' => $img_size['height']
			                        ));

			return array(
				'img_width'  => $im['img_width'],
				'img_height' => $im['img_height']
			);
		}
	}

	private function loadImage()
	{
		global $ibforums, $std;
		$filename = $ibforums->vars['upload_dir'] . "/" . $this->realFilename();
		$finfo    = new finfo(FILEINFO_MIME);
		$mime     = $finfo->file($filename);

		switch ($mime)
		{
			case 'image/jpeg':
				return imagecreatefromjpeg($filename);
			case 'image/png':
				return imagecreatefrompng($filename);
			case 'image/gif':
				//return @imagecreatefromgif($filename);
		}

		return NULL;
	}

	private function saveImage($img)
	{
		$filename = $this->previewPath();
		$finfo    = new finfo(FILEINFO_MIME);
		$mime     = $finfo->file($filename);

		switch ($mime)
		{
			case 'image/jpeg':
				return imagejpeg($img, $filename);
			case 'image/png':
				return imagepng($img, $filename);
			case 'image/gif':
				// return imagegif($img, $filename);
		}
		return false;
	}

	private function previewPath()
	{
		global $ibforums;
		return $ibforums->vars['upload_dir'] . "/preview/" . $this->realFilename();
	}

	private function previewUrl()
	{
		global $ibforums;
		return $ibforums->vars['upload_url'] . "/preview/" . $this->realFilename();
	}

	private function realImageUrl()
	{
		return $this->getHref();
	}

	public function getRealSize(){
		$img_size = @GetImageSize(Ibf::app()->vars['upload_dir'] . "/" . $this->realFilename());
		return [
			'width' => $img_size[0],
		    'height' => $img_size[1]
		];
	}
}
