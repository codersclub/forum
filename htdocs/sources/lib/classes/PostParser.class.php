<?php

/*
+--------------------------------------------------------------------------
|   Invision Power Board v1.2
|   ========================================
|   by Matthew Mecham
|   (c) 2001 - 2003 Invision Power Services
|   http://www.invisionpower.com
|   ========================================
|   Web: http://www.invisionboard.com
|   Email: matt@invisionpower.com
|   Licence Info: http://www.invisionboard.com/?license
+---------------------------------------------------------------------------
|
|   > Text processor module
|   > Module written by Matt Mecham
|   > Official Version: 1.2 - Number of changes to date 3 billion (estimated)
|
+--------------------------------------------------------------------------
*/

class syntax_cfg
{
	var $id;
	var $syntax;
	var $back_color;
	var $fore_color;
	var $tab_length;
	var $version = 0;

	var $rules = array();
}

class syntax_rule
{
	var $reg_exp = '';
	var $tags = array();
	var $actions = array();

	function syntax_rule($db_row)
	{
		$this->reg_exp = $db_row ['reg_exp'];

		for ($n = 0; $n < 10; $n++)
		{
			$this->tags[$n]    = $db_row ['tag_' . $n];
			$this->actions[$n] = $db_row ['action_' . $n];
		}
	}
}

class PostParser
{

	var $error = "";
	var $image_count = 0;
	var $emoticon_count = 0;
	var $quote_html = array();
	var $quote_open = 0;
	var $quote_closed = 0;
	var $quote_error = 0;
	var $emoticons = "";
	var $badwords = "";
	var $strip_quotes = "";
	var $in_sig = "";
	var $allow_unicode = 1;
	var $file = array();
	var $cfg = array();
	var $syntax = array();
	var $code_text = array();
	var $code_counter = 0;
	var $cache_posts = array();
	var $absent_highlight = array();
	var $code_count = 0;
	
	/**
	 * @var int
	 */
	private $topic_id;
	public $rss_mode = false;

	public $attachments = array();
	public $attachments_to_render = array();

	public $post_attachments = array();
	public $siu_thumb = false;

	// ******************************************************************************
	// ***  Highlight Functions (c) by Leprecon
	// ***  Modified by Song, 24.12.04 for local client highlight
	// ******************************************************************************

	function syntax_load_by_code($syntax, $method)
	{
		global $ibforums;

		if (!isset($this->absent_highlight[$syntax]))
		{
			$stmt = $ibforums->db->query("SELECT
				id,
				syntax,
				back_color,
				fore_color,
				version,
				tab_length
			    FROM ibf_syntax_list
			    WHERE syntax='" . addslashes($syntax) . "'");

			if ($row = $stmt->fetch())
			{
				$cfg = new syntax_cfg;

				$cfg->id         = $row['id'];
				$cfg->syntax     = $row['syntax'];
				$cfg->back_color = $row['back_color'];
				$cfg->fore_color = $row['fore_color'];
				$cfg->tab_length = $row['tab_length'];
				$cfg->version    = $row['version'];

				// load rules
				if ($method == 'server')
				{
					$n = 0;

					$stmt = $ibforums->db->query("SELECT *
					    FROM ibf_syntax_rules
					    WHERE syntax_id='" . $cfg->id . "'
					    ORDER BY record");

					while ($db_row = $stmt->fetch())
					{
						$cfg->rules[$n++] = new syntax_rule ($db_row);
					}
				}

				return $cfg;

			} else
			{
				$this->absent_highlight[$syntax] = $syntax;
			}
		}

		return '';

	}

	function syntax_load_by_id($id, $method)
	{
		global $ibforums;

		$stmt = $ibforums->db->query("SELECT
			id,
			syntax,
			back_color,
			fore_color,
			version,
			tab_length
		    FROM ibf_syntax_list
		    WHERE id='" . $id . "'");

		if ($row = $stmt->fetch())
		{
			$cfg = new syntax_cfg;

			$cfg->id         = $row['id'];
			$cfg->syntax     = $row['syntax'];
			$cfg->back_color = $row['back_color'];
			$cfg->fore_color = $row['fore_color'];
			$cfg->tab_length = $row['tab_length'];
			$cfg->version    = $row['version'];

			// load rules
			if ($method == 'server')
			{
				$n    = 0;
				$stmt = $ibforums->db->query("SELECT *
				    FROM ibf_syntax_rules
				    WHERE syntax_id='" . $cfg->id . "'
				    ORDER BY record");

				while ($db_row = $stmt->fetch())
				{
					$cfg->rules[$n++] = new syntax_rule($db_row);
				}
			}

			return $cfg;

		} else
		{
			return '';
		}

	}

	function prepare_code_tabs($code, $tab = 4)
	{
		// catch special BOLD {b} tag inside CODE tag
		$code = preg_replace("#\{b\}(.+?)\{/b\}#is", Chr(1050) . "\\1" . Chr(1051), $code);

		$code = preg_replace("'(&#60;|&#060;|&lt;)'i", "<", $code);
		$code = preg_replace("'(&#62;|&#062;|&gt;)'i", ">", $code);
		$code = preg_replace("'(&quot;|&#034;|&#34;)'i", "\"", $code);
		$code = preg_replace("'(&#039;|&#39;)'i", "'", $code);
		$code = preg_replace("'(&#124;)'i", "|", $code);
		$code = preg_replace("'(&#036;|&#36;)'i", "$", $code);
		$code = preg_replace("'(&#092;|&#92;)'i", "\\", $code);
		$code = preg_replace("'(&#033;|&#33;)'i", "!", $code);
		$code = preg_replace("'&amp;'i", "&", $code);

		$code = preg_replace("#\[#", "[", $code);
		$code = preg_replace("#\]#", "]", $code);

		$view  = '';
		$lines = explode("\n", $code);

		// Convert tabs to ' ';
		foreach ($lines as $line)
		{
			$words = explode(chr(9), $line);

			$line = $words[0];
			$col  = strlen($line);
			$size = sizeof($words);

			for ($n = 1; $n < $size; $n++)
			{
				$pad = $col % $tab;
				if ($pad == 0)
				{
					$col += $tab;
				} else
				{
					$col += $tab - $pad;
				}

				$word = $words[$n];
				$line = str_pad($line, $col) . $word;
				$col += strlen($word);
			}

			$view .= $line . "\n";
		}

		return rtrim($view, "\n\r"); // delete last "\n"
	}

	function syntax_code_to_view($code)
	{

		$view = preg_replace("#&#", "&amp;", $code);
		$view = preg_replace("#\[#", "[&shy;", $view);
		$view = preg_replace("#\]#", "&shy;]", $view);

		$view = preg_replace("/\[/", "[&shy;", $view);
		$view = preg_replace("/\]/", "&shy;]", $view);

		return $view;

	}

	///////////////////////////////////////////////////////////////////////////
	function syntax_find_by_code($syntax, $method)
	{
		foreach ($this->syntax as $cfg)
		{
			if ($cfg->syntax == $syntax)
			{
				return $cfg;
			}
		}

		$cfg = $this->syntax_load_by_code($syntax, $method);

		if ($cfg)
		{
			$count                 = sizeof($this->syntax);
			$this->syntax [$count] = $cfg;

			return $cfg;

		} else
		{
			return '';
		}

	}

	///////////////////////////////////////////////////////////////////////////
	function syntax_find_by_id($syntax_id, $method)
	{

		foreach ($this->syntax as $cfg)
		{
			if ($cfg->id == $syntax_id)
			{
				return $cfg;
			}
		}

		$cfg = $this->syntax_load_by_id($syntax_id, $method);

		if ($cfg != '')
		{
			$count                 = sizeof($this->syntax);
			$this->syntax [$count] = $cfg;

			return $cfg;

		} else
		{
			return '';
		}

	}

	/**
	 *
	 * возвращает валидный, с точки зрения html, цвет
	 * @param string $val
	 * @return string
	 */
	static function color($val)
	{
		if (preg_match('!^([0-9a-f]{6}|[0-9a-f]{3})$!i', $val))
		{
			return '#' . $val;
		} else
		{
			return $val;
		}
	}

	///////////////////////////////////////////////////////////////////////////

	function regex_code_syntax($code, $syntax, $id)
	{
		global $ibforums, $print, $sess;

		$this->code_count++;

		if (preg_match("/\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\]/i", $txt))
		{
			return "\[code\]$code\[/code\]";
		}

		$cfg = '';

		if ($ibforums->member['id'])
		{
			if ($ibforums->member['syntax'] != 'none')
			{
				$temp = $ibforums->member['syntax'];

				// server highlight for forumizer
				if ($ibforums->vars['plg_offline_client'])
				{
					$temp = 'server';
				}

				$length = strlen($code);

				// Song * Opera tool
				// Client highlight is very slow for large text,
				// so, reset highlight to server if current browser
				// Opera and length of code tag is more than 10 kb.

				if ($temp == 'client' && $length > 10 * 1024 && strpos($sess->user_agent, 'Opera') !== FALSE)
				{
					$temp = 'server';
				}

				// highlight code tag only if length code tag text is less than 50 kb
				if ($length < 50 * 1024 or $temp == 'client')
				{
					if (!$syntax)
					{
						if ($id != -1)
						{
							$cfg = $this->syntax_find_by_id($id, $temp);
						}
					} else
					{
						// Song * patch for 1C syntax
						// (because there is confusing with
						// russian "C" letter), 03.03.05

						if ($syntax == "1С" or $syntax == "1с")
						{
							$syntax = "1C";
						}

						if ($syntax != 'no')
						{
							$cfg = $this->syntax_find_by_code($syntax, $temp);
						}
					}
				}
			}
		}

		if ($cfg)
		{
			$code = $this->prepare_code_tabs($code, $cfg->tab_length);

			if ($temp == 'server')
			{
				$view = '';

				$pos    = 0;
				$match  = array();
				$length = strlen($code);

				while ($pos < $length)
				{
					$result = false;

					foreach ($cfg->rules as $rule)
					{
						$result = preg_match($rule->reg_exp, $code, $match);

						if ($result)
						{
							break;
						}
					}

					if ($result)
					{
						$l = 0;

						for ($n = 0; $n < 10; $n++)
						{
							if ($rule->actions[$n] == 'tag')
							{
								$txt = $this->syntax_code_to_view($match[$n]);

								$txt = $rule->tags[$n] . str_replace("\n", $rule->tags[($n + 1) % 10] . "**[*]**" . $rule->tags[$n], $txt) . $rule->tags[($n + 1) % 10];

								// пустой текст не надо подсвечивать
								$view .= str_replace($rule->tags[$n] . $rule->tags[($n + 1) % 10], '', $txt);

								$l += strlen($match[$n]);
							}

							if ($rule->actions[$n] == 'none')
							{
								$view .= $rule->tags[$n] . $rule->tags[($n + 1) % 10];
								$l += strlen($match[$n]);
							}

							if ($rule->actions[$n] == 'value')
							{
								$view .= $match[$n];
								$l += strlen($match[$n]);
							}

							if ($rule->actions[$n] == 'count')
							{
								$l += strlen($match[$n]);
							}
						}

						$pos += $l;
						$code = substr($code, $l);
					} else
					{
						$view .= $this->syntax_code_to_view($code[0]);

						$pos++;
						$code = substr($code, 1);
					}
				}

				$view = $this->regex_clean_code($view);

				$view = str_replace(array("<br>", '**[*]**'), "</li><li>", $view);
				$view = "<ol type=\"1\"><li>{$view}</li></ol>";

				$view = str_replace('<li></li>', '<li>&nbsp;</li>', $view);
				$view = str_replace('<li> ', '<li>&nbsp;', $view);
				// server highlight form
				$view = sprintf('<div style=\'color:%s; background-color:%s\'>%s</div>', self::color($cfg->fore_color), self::color($cfg->back_color), $view);
				//
			} else
			{
				$code = $this->syntax_code_to_view($code);
				$code = $this->regex_clean_code($code);

				$syntax = str_replace('#', 'sharp', $cfg->syntax);

				// add included to page highlights to array
				if (!isset($print->syntax[$syntax]))
				{
					$print->syntax[$syntax] = $cfg->version;
				}

				$view = sprintf('<div style=\'color:%s; background-color:%s\' id=\'code_%d\'>%s</div>', self::color($cfg->fore_color), self::color($cfg->back_color), $this->code_count, $code);
			}
		} else
		{
			$view = $this->prepare_code_tabs($code);
			$view = $this->syntax_code_to_view($view);
			$view = $this->regex_clean_code($view);
			$view = str_replace("<br>", "</li><li>", $view);
			$view = "<div><ol type=\"1\"><li>{$view}</li></ol></div>";
			$view = preg_replace('#<li>\s*?</li>#', '<li>&nbsp;</li>', $view);
			$view = preg_replace('#<li> #', '<li>&nbsp;', $view);
		}
		$use_line_numbering = $ibforums->member['syntax_use_line_numbering'] !== NULL
			? $ibforums->member['syntax_use_line_numbering']
			: false;
		if (!$use_line_numbering)
		{
			$view = str_replace(array('<li>', '</li>'), array('<div class="code_line">', '</div>'), $view);
		}
		$html = $this->wrap_style(array(
		                               'STYLE'   => 'CODE',
		                               'BCOLOR'  => $cfg->back_color,
		                               'FCOLOR'  => $cfg->fore_color,
		                               'CODE_ID' => $this->code_count
		                          ));

		// vot	15/03/06
		$view = "{$html['START']}<div>$view</div>{$html['END']}";
		if ($temp == 'client' && $cfg)
		{
			$use_line_numbering = $use_line_numbering
				? 'true'
				: 'false';
			$view .= "<script>parseOne('code_{$this->code_count}','{$syntax}',$use_line_numbering)</script>";
		}
		if ($this->code_count < 2)
		{
			$view .= "<script>preloadCodeButtons('{$this->code_count}');</script>";
			if ($ibforums->member['syntax_show_controls'] == 'auto')
			{
				$view .= "<script>syntax_add_show_controls_on_mouseenter();</script>";
			}
		}

		return $view;

	}

	function regex_clean_code($code)
	{

		//$code = preg_replace( "/\[\*\] /m"	 , "[*]&nbsp;"	, $code);
		$code = preg_replace("/  /", " &nbsp;", $code);
		$code = preg_replace("#<#", "&#60;", $code);
		$code = preg_replace("#>#", "&#62;", $code);
		$code = preg_replace("#\)#", ")", $code);
		$code = preg_replace("#\(#", "(", $code);
		$code = preg_replace("#\"#", "&quot;", $code);
		$code = preg_replace("#\r\n|\n|\r#", "<br>", $code);
		//$code = preg_replace( "#\n#"     , "<br>"	, $code );

		$code = preg_replace("#:#", "&shy;:&shy;", $code);
		$code = preg_replace("#\)#", "&shy;)", $code);
		$code = preg_replace("#\(#", "&shy;(", $code);
		$code = preg_replace("#'#", "&#39;", $code);

		// extract special tag
		$code = preg_replace("#" . Chr(1050) . "(.+?)" . Chr(1051) . "#is", "<span style='color:red'>\\1</span>", $code);

		return $code;

	}

	//суть функции в том, чтобы при парсинге узнать соответствие открытых и закрытых тегов (с учетом их порядка)
	//чтобы потом решить - что с ними делать.
	//
	//Результаты работы функии (после обработки последнего [/spoiler] в посте):
	//quote_open - количество [spoiler] оставшиеся без пары
	//quote_error - количество [/spoiler] без пары
	//все закрывающие [/spoiler] без пары останутся необработанными, однако открывающие теги будут обработаны!!!
	//за исключением тех, которые находятся дальше последнего [/spoiler], ибо они сюда даже не попадут
	private function convert_spoiler($matches)
	{
		global $ibforums;
		//Проверка matches[4] здесь - выяснение, сработало вхождение [spoiler] или [/spoiler].
		if (!$matches[4])
		{
			// Starting Tag
			$this->quote_open++;
			return '<div class=\'spoiler closed\'><div class=\'spoiler_header\' onclick=\'openCloseParent(this)\'>' . (trim($matches[3])
				? $matches[3]
				: $ibforums->lang["spoiler"]) . '</div><div class=\'body\'>';
		} elseif ($this->quote_open > 0)
		{
			// Ending Tag
			$this->quote_open--;
			return "</div></div>";
		} else
		{
			// Leave As Is
			$this->quote_error++;
			return $matches[0];
		}
	}

	/**************************************************/
	// vot:
	// regex_parse_spoiler: Builds this [SPOILER] .. [/SPOILER] tag HTML
	/**************************************************/
	function regex_parse_spoiler($the_txt = "")
	{
		global $ibforums;

		if ($the_txt == "")
		{
			return;
		}

		$txt               = $the_txt;
		$this->quote_open  = 0;
		$this->quote_error = 0;

		$txt = preg_replace_callback("#(\[spoiler(=([^\]]*?))?\])|(\[/spoiler\])#is", array(
		                                                                                   &$this,
		                                                                                   'convert_spoiler'
		                                                                              ), $txt);

		if ($this->quote_open || $this->quote_error)
		{
			//Можно просто закрыть все оставшиеся теги спойлера
			//$txt .= str_repeat('</div></div>', $this->quote_open);
			//а можно ещё проще вернуть всё фсад
			$txt = $the_txt;
		}

		$this->quote_open  = 0;
		$this->quote_error = 0;

		$txt = preg_replace("/\n/", "<br>", $txt);

		return $txt;

	}

	function regex_pre_tag($txt = "")
	{
		$txt = preg_replace("!<br[ /]*>!", "", $txt);
		return "<pre>" . $txt . "</pre>";
	}

	function smilie_length_sort($a, $b)
	{
		if (strlen($a['typed']) == strlen($b['typed']))
		{
			return 0;
		}
		return (strlen($a['typed']) > strlen($b['typed']))
			? -1
			: 1;
	}

	function word_length_sort($a, $b)
	{
		if (strlen($a['type']) == strlen($b['type']))
		{
			return 0;
		}
		return (strlen($a['type']) > strlen($b['type']))
			? -1
			: 1;
	}

	///////////////////////////////////////////////////////////

	function __construct($load = 0)
	{
		global $ibforums;

		$this->strip_quotes = $ibforums->vars['strip_quotes'];

		if ($load)
		{
			// Pre-load the bad words
			$stmt = $ibforums->db->query("SELECT * FROM ibf_badwords");

			if ($stmt->rowCount())
			{
				while ($r = $stmt->fetch())
				{
					$this->badwords[] = array(
						'type'    => stripslashes($r['type']),
						'swop'    => stripslashes($r['swop']),
						'm_exact' => $r['m_exact'],
					);
				}

			} else
			{
				$this->no_bad_words = 1;
			}

			if ($ibforums->member['id'] and (!$ibforums->member['view_img'] or !$ibforums->member['sskin_id']))
			{
				return;
			}

			// Pre-load the smilies
			$this->emoticons = array();

			// Song * smile skin

			if (!$ibforums->member['id'])
			{
				$id = 1;
			} else
			{
				$id = $ibforums->member['sskin_id'];
			}
			if (!$id)
			{
				$id = 1;
			}
			$stmt = $ibforums->db->query("SELECT
					typed,
					image,
					clickable
				    FROM ibf_emoticons
				    WHERE skid='" . $id . "'");

			if ($stmt->rowCount())
			{
				while ($r = $stmt->fetch())
				{
					$this->emoticons[] = array(
						'typed'     => stripslashes($r['typed']),
						'image'     => stripslashes($r['image']),
						'clickable' => $r['clickable'],
					);
				}
			}
		}
	}

	/**************************************************/
	// PARSE POLL TAGS
	// Converts certain code tags for polling
	/**************************************************/

	function parse_poll_tags($txt)
	{

		// if you want to parse more tags for polls, simply cut n' paste from the "convert" routine
		// anywhere here.

		$txt = preg_replace("#\[img\s*=\s*\&quot\;\s*(.*?)\s*\&quot\;\s*\](.*?)\[\/img\]#ie", "\$this->regex_check_image('\\2','\\1')", $txt);
		$txt = preg_replace("#\[img\s*=\s*(.*?)\s*\](.*?)\[\/img\]#ie", "\$this->regex_check_image('\\2','\\1')", $txt);
		$txt = preg_replace("#\[img\](.+?)\[/img\]#ie", "\$this->regex_check_image('\\1')", $txt);

		$txt = preg_replace("#\[url\](\S+?)\[/url\]#ie", "\$this->regex_build_url(array('html' => '\\1', 'show' => '\\1'))", $txt);
		$txt = preg_replace("#\[url\s*=\s*\&quot\;\s*(\S+?)\s*\&quot\;\s*\](.*?)\[\/url\]#ie", "\$this->regex_build_url(array('html' => '\\1', 'show' => '\\2'))", $txt);
		$txt = preg_replace("#\[url\s*=\s*(\S+?)\s*\](.*?)\[\/url\]#ie", "\$this->regex_build_url(array('html' => '\\1', 'show' => '\\2'))", $txt);

		while (preg_match("#\[color=([a-zA-Z0-9]*)\](.+?)\[/color\]#ies", $txt))
		{
			$txt = preg_replace("#\[color=([a-zA-Z0-9]*)\](.+?)\[/color\]#ies", "\$this->regex_font_attr(array('s'=>'col' ,'1'=>'\\1','2'=>'\\2'))", $txt);
		}

		return $txt;
	}

	/**************************************************/
	// PARSE [attach] TAGS
	/**************************************************/

	function parse_attach_tags(&$txt)
	{
		/* valid tags are:
		 * [attach=0000]main attach, from post_attachments[/attach]
		 * [attach=p0000]attach from posts[/attach]
		 * [attach=00000,link,noinfo]...[/attach]
		 */
		$this->attachments      = array();
		$this->post_attachments = array();
		$txt                    = preg_replace_callback('#\[attach\s*=\s*(p?)(\d+)?\s*\](.*?)\[/attach\]#i', array(
		                                                                                                          $this,
		                                                                                                          'regex_attach'
		                                                                                                     ), $txt);

		return $txt;
	}

	function regex_attach($matches)
	{
		global $ibforums;
		/*
		 * $matches[0] = '[attach=00000,link]...[/attach]'
		 * $matches[1] = { 'p' | '' }
		 * $matches[2] = <id>
		 * $matches[3] = <текст>
		 */

		list(, $p, $id, $text) = $matches;

		if (!trim($p))
		{
			$this->attachments[] = $id;
		} else
		{
			$this->post_attachments[] = $id;
		}

		$attach = NULL;
		if ($p == '')
		{
			$attach = $this->attachments_to_render[$id]
				? : Attachment::getById($id);
		} elseif ($p == 'p')
		{
			$stmt = $ibforums->db->query("SELECT
					pid,
					attach_id,
					attach_type,
					attach_file,
					attach_size,
					attach_hits
				    FROM ibf_posts
				    WHERE pid='" . $id . "'");

			$row = $stmt->fetch();
			if ($row && $row['attach_id'])
			{
				$attach = Attachment::createFromPostRow($row);
			}
		} else
		{
			return $text;
		}
		if (!($attach instanceof Attachment))
		{
			return $text;
		}
		$text = $this->render_attach($attach, $text);
		return trim($text);
	}

	public function render_attach(Attachment $attach, $text)
	{

		global $ibforums, $std;

		$preview_enabled = ($this->siu_thumb and $ibforums->member['view_img'] or $ibforums->vars['show_img_upload']);

		if ($attach->isImage() and $preview_enabled)
		{
			$text = $this->renderPreview($attach, $text);
		} else
		{

			!trim($text) && $text = $ibforums->lang['attached_file'];

			$text = "<strong><span class='edit'>{$text}:</strong>&nbsp;{$attach->getLink()}";
			$text .= " ({$attach->sizeAsString()}, {$ibforums->lang['attach_hits']}: {$attach->hits()})";
			$text .= "</span>";
		}
		return $text;
	}

	private function renderPreview(Attachment $attach, $text)
	{
		global $ibforums, $std;
		$alt = htmlspecialchars("{$ibforums->lang['pic_attach_thumb']} {$ibforums->lang['pic_zoom_thumb']}");
		if ($ibforums->vars['siu_width'] AND $ibforums->vars['siu_height'])
		{

			if (!trim($text))
			{
				$text = $ibforums->lang['pic_attach_thumb'];
			}

			$img_size = $attach->getPreviewSizes();
			$text     = "<span class='attach_preview'><p>{$text}:</p><br><a href='{$attach->getHref()}' title='{$alt}' target='_blank'><img src='{$attach->getPeviewLink()}' width='{$img_size['img_width']}' height='{$img_size['img_height']}' class='attach' alt='{$alt}'></a></span>";

		} else
		{

			if (!trim($text))
			{
				$text = $ibforums->lang['pic_attach'];
			}

			$text = "<span class='attach_preview'><p>{$text}:</p><img src='{$ibforums->base_url}act=Attach&amp;type={$attach->itemType()}&amp;id={$attach->itemId()}&amp;attach_id={$attach->attachId()}' class='attach' alt='{$alt}'></span>";

		}
		return $text;
	}

	// Song + Shaman * parse smiles, smile skin
	function prepareIcons()
	{
		global $ibforums;

		if ($ibforums->member['id'] and (!$ibforums->member['view_img'] or !$ibforums->member['sskin_id']))
		{
			return;
		}

		if (!is_array($this->emoticons))
		{

			// Song * smile skin

			if (!$ibforums->member['id'])
			{
				$id = 1;
			} else
			{
				$id = $ibforums->member['sskin_id'];
			}
			if (!$id)
			{
				$id = 1;
			}
			$stmt = $ibforums->db->query("SELECT
				typed,
				image,
				clickable
			    FROM ibf_emoticons
			    WHERE skid='" . $id . "'");

			// /Song * smile skin

			$this->emoticons = array();

			if ($stmt->rowCount())
			{
				while ($r = $stmt->fetch())
				{
					$this->emoticons[] = array(
						'typed'     => stripslashes($r['typed']),
						'image'     => stripslashes($r['image']),
						'clickable' => $r['clickable']
					);
				}
			}
		}

	}

	function parse_smiles($txt)
	{
		global $ibforums;

		if ($ibforums->member['id'] and (!$ibforums->member['view_img'] or !$ibforums->member['sskin_id']))
		{
			return $txt;
		}

		$this->PrepareIcons();

		$txt = " " . $txt . " ";

		usort($this->emoticons, array('PostParser', 'smilie_length_sort'));

		if (count($this->emoticons) > 0)
		{
			foreach ($this->emoticons as $a_id => $row)
			{
				$code  = $row['typed'];
				$image = $row['image'];

				// Make safe for regex
				$code = preg_quote($code, "/");
				$txt  = preg_replace("!(?<=[^\w&;])$code(?=.\W|\W.|\W$)!e", "\$this->convert_emoticon('$code', '$image')", $txt);
			}
		}

		return $txt;

	}

	// Song * message for moderator only, 03.11.2004
	function regex_moderator_message($message)
	{
		global $ibforums;

		if (!$message or !$ibforums->member['id'])
		{
			return "";
		}
		if (!($ibforums->member['is_mod'] or $ibforums->member['g_is_supmod']))
		{
			return "";
		}

		return "<div class='mstop'>{$ibforums->lang['mod_mes']}</div><div class='msmain'>{$message}</div>";

	}

	function regex_global_moderator_message($message, $mid = 0)
	{
		global $ibforums;

		if (!$ibforums->member['id'] or !$message)
		{
			return "";
		}

		if (!$ibforums->member['g_is_supmod'])
		{
			if (!$mid)
			{
				return "";
			}

			if ($ibforums->member['id'] != $mid)
			{
				return "";
			}
		}

		return "<div class='gmstop'>{$ibforums->lang['glob_mod_mes']}</div><div class='gmsmain'>{$message}</div>";

	}

	// *******************************************************************
	// *********************** Main Draw Function ************************
	// *******************************************************************

	// Shaman * Main Draw Function

	function prepare($in = array(
		'TEXT'      => "",
		'SMILIES'   => 0,
		'CODE'      => 0,
		'SIGNATURE' => 0,
		'HTML'      => 0,
		'HID'       => -1,
		'TID'       => 0,
		'MID'       => 0,
	))
	{

		global $ibforums, $std;

		if (!isset($in['CODE']))
		{
			$in['CODE'] = 0;
		}
		if (!isset($in['SMILIES']))
		{
			$in['SMILIES'] = 0;
		}
		if (!isset($in['SIGNATURE']))
		{
			$in['SIGNATURE'] = 0;
		}
		if (!isset($in['TID']))
		{
			$in['TID'] = 0;
		}
		if (!isset($in['MID']))
		{
			$in['MID'] = 0;
		}
		if (!isset($in['HID']))
		{
			$in['HID'] = -1;
		}

		$this->attachments_to_render = isset($in['ATTACHMENTS'])
			? Attachment::reindexArray($in['ATTACHMENTS'])
			: array();

		$this->topic_id = isset($in['TID']) ? intval($in['TID']) : 0;
		
		$this->in_sig = $in['SIGNATURE'];
		$txt          = $in['TEXT'];

		//--------------------------------------
		// Are we parsing iB_CODE and do we have either '[' or ']' in the
		// text we are processing?
		//--------------------------------------

		// Song * do not parse message if "[" is absent in it's body,
		// but parse smiles

		if (strpos($txt, "[") === FALSE)
		{
			//--------------------------------------
			// Auto parse URLs
			//--------------------------------------

			//there can't be letters or digits before protocol descriptions,
			//also we check for _ for historical reasons
			$txt = preg_replace(
				"#([^a-z0-9_]?)((http|https|news|ftp)://\w+[^\s\[\]<$]+)#ie",
				"\$this->regex_build_url(array('html' => '\\2', 'show' => '', 'st' => '\\1'))",
				$txt
			);

			// Swop \n back to <br>
			$txt = preg_replace("/\n/", "<br>", $txt);

			// Unicode?
			if ($this->allow_unicode)
			{
				# &#174;
				$txt = preg_replace("/&amp;#([0-9]+);/s", "&#\\1;", $txt);
				# &#x00FF;
				$txt = preg_replace("/&amp;#x([0-9A-F]+);/s", "&#x\\1;", $txt);
				# &reg; &copy;
				$txt = preg_replace("/&amp;(\w+);/s", "&\\1;", $txt);
			}

			if ($in['SMILIES'] != 0 and $in['SIGNATURE'] == 0 and $ibforums->member['view_img'])
			{
				$txt = $this->parse_smiles($txt);
			}

			return $txt;
		}

		//--------------------------------------
		// Tables by vot (Convert)
		//--------------------------------------

		$txt = preg_replace("#\[table\](.*?)\[/table\]#ies", "\$this->regex_table('\\1')", $txt);

		//--------------------------------------
		// Align Hack by Farch (Convert)
		//--------------------------------------

		$txt = preg_replace("#\[r\](.+?)\[/r\]#is", "<div align=right>\\1</div>", $txt);
		$txt = preg_replace("#\[l](.+?)\[/l\]#is", "<div align=left>\\1</div>", $txt);
		$txt = preg_replace("#\[c\](.+?)\[/c\]#is", "<div align=center>\\1</div>", $txt);
		$txt = preg_replace("#\[hr\]#is", "<hr>", $txt);

		if ($in['CODE'] == 1)
		{

			//---------------------------------
			// Do [QUOTE(name,date)] tags
			//---------------------------------

			// Find the first, and last quote tag (greedy match)...

			$this->quote_open   = 0;
			$this->quote_error  = 0;
			$this->quote_closed = 0;

			$txt = preg_replace_callback('#\[quote(?:[^\]]*)\].*\[/quote\]#is', [$this, 'regex_parse_quotes'], $txt );
				
			//---------------------------------
			// Do [CODE] tag
			//---------------------------------
			$txt = preg_replace("#\[code\s*?(=\s*?(.*?)|)\s*\](.*?)\[/code\]#ies", "\$this->regex_code_syntax('\\3', '\\2', '{$in['HID']}')", $txt);

			$txt = $this->parse_attach_tags($txt);
			if ($in['SIGNATURE'] != 1)
			{

				// ******************************************** COMMENTED BY SONG **********************************************

				//			$txt = preg_replace( "#\[sql\](.+?)\[/sql\]#ies"    , "\$this->regex_sql_tag('\\1')"    , $txt );
				//			$txt = preg_replace( "#\[html\](.+?)\[/html\]#ies"  , "\$this->regex_html_tag('\\1')"   , $txt );

				// ******************************************** COMMENTED BY SONG **********************************************

				//-------------------------
				// [LIST]    [*]    [/LIST]
				//-------------------------

				while (preg_match("#\n?\[list\](.+?)\[/list\]\n?#ies", $txt))
				{
					$txt = preg_replace("#\n?\[list\](.+?)\[/list\]\n?#ies", "\$this->regex_list('\\1')", $txt);
				}

				while (preg_match("#\n?\[list=(a|A|i|I|1)\](.+?)\[/list\]\n?#ies", $txt))
				{
					$txt = preg_replace("#\n?\[list=(a|A|i|I|1)\](.+?)\[/list\]\n?#ies", "\$this->regex_list('\\2','\\1')", $txt);
				}
			}

			//---------------------------------
			// Do [SPOILER] tags
			//---------------------------------

			// Find the first, and last quote tag (greedy match)...

			$txt = preg_replace("#(\[spoiler(=.+?)?].*\[/spoiler\])#ies", "\$this->regex_parse_spoiler('\\1')", $txt);

			//---------------------------------
			// Do [IMG] [FLASH] tags
			//---------------------------------

			if ($ibforums->vars['allow_images'])
			{
				$txt = preg_replace("#\[img\s*=\s*\&quot\;\s*(.*?)\s*\&quot\;\s*\](.*?)\[\/img\]#ie", "\$this->regex_check_image('\\2','\\1')", $txt);
				$txt = preg_replace("#\[img\s*=\s*(.*?)\s*\](.*?)\[\/img\]#ie", "\$this->regex_check_image('\\2','\\1')", $txt);
				$txt = preg_replace("#\[img\](.+?)\[/img\]#ie", "\$this->regex_check_image('\\1')", $txt);
				$txt = preg_replace("#(\[flash=)(\S+?)(\,)(\S+?)(\])(\S+?)(\[\/flash\])#ie", "\$this->regex_check_flash('\\2','\\4','\\6')", $txt);
			}

			// Start off with the easy stuff
			$txt = preg_replace("#\[b\](.*?)\[/b\]#is", "<b>\\1</b>", $txt);
			$txt = preg_replace("#\[i\](.*?)\[/i\]#is", "<i>\\1</i>", $txt);
			$txt = preg_replace("#\[u\](.*?)\[/u\]#is", "<u>\\1</u>", $txt);
			$txt = preg_replace("#\[s\](.*?)\[/s\]#is", "<s>\\1</s>", $txt);
			// barazuk: [o]
			$txt = preg_replace("#\[o\](.*?)\[/o\]#is", "<SPAN style='text-decoration: overline;'>\\1</SPAN>", $txt);

			// vot: SUBscript & SUPERscript
			$txt = preg_replace("#\[sub\](.*?)\[/sub\]#is", "<sub>\\1</sub>", $txt);
			$txt = preg_replace("#\[sup\](.*?)\[/sup\]#is", "<sup>\\1</sup>", $txt);

			// (c) (r) and (tm)
			$txt = preg_replace("#\(c\)#i", "&copy;", $txt);
			$txt = preg_replace("#\(tm\)#i", "&#153;", $txt);
			$txt = preg_replace("#\(r\)#i", "&reg;", $txt);

			// email tags
			// [email]matt@index.com[/email]   [email=matt@index.com]Email me[/email]

			// ******************************************** COMMENTED BY SONG **********************************************

			//		$txt = preg_replace( "#\[email\](\S+?)\[/email\]#i"                                                                , "<a href='mailto:\\1'>\\1</a>", $txt );
			//		$txt = preg_replace( "#\[email\s*=\s*\&quot\;([\.\w\-]+\@[\.\w\-]+\.[\.\w\-]+)\s*\&quot\;\s*\](.*?)\[\/email\]#i"  , "<a href='mailto:\\1'>\\2</a>", $txt );
			//		$txt = preg_replace( "#\[email\s*=\s*([\.\w\-]+\@[\.\w\-]+\.[\w\-]+)\s*\](.*?)\[\/email\]#i"                       , "<a href='mailto:\\1'>\\2</a>", $txt );

			// ******************************************** COMMENTED BY SONG **********************************************

			// url tags
			// [url]http://www.index.com[/url]   [url=http://www.index.com]ibforums![/url]

			$txt = preg_replace("#\[url\](\S+?)\[/url\]#ie", "\$this->regex_build_url(array('html' => '\\1', 'show' => '\\1'))", $txt);
			$txt = preg_replace("#\[url\s*=\s*\&quot\;\s*(\S+?)\s*\&quot\;\s*\](.*?)\[\/url\]#ie", "\$this->regex_build_url(array('html' => '\\1', 'show' => '\\2'))", $txt);
			$txt = preg_replace("#\[url\s*=\s*(\S+?)\s*\](.*?)\[\/url\]#ie", "\$this->regex_build_url(array('html' => '\\1', 'show' => '\\2'))", $txt);

			// font size, colour and font style
			// [font=courier]Text here[/font]  [size=6]Text here[/size]  [color=red]Text here[/color]

			while (preg_match("#\[size=([^\]]+)\](.+?)\[/size\]#ies", $txt))
			{
				$txt = preg_replace("#\[size=([^\]]+)\](.+?)\[/size\]#ies", "\$this->regex_font_attr(array('s'=>'size','1'=>'\\1','2'=>'\\2'))", $txt);
			}

			while (preg_match("#\[font=([^;<>\*\(\)\]\"']*)\](.*?)\[/font\]#ies", $txt))
			{
				$txt = preg_replace("#\[font=([^;<>\*\(\)\"']*)\](.*?)\[/font\]#ies", "\$this->regex_font_attr(array('s'=>'font','1'=>'\\1','2'=>'\\2'))", $txt);
			}

			while (preg_match("#\[color=([a-zA-Z0-9]*)\](.*?)\[/color\]#ies", $txt))
			{
				$txt = preg_replace("#\[color=([a-zA-Z0-9]*)\](.*?)\[/color\]#ies", "\$this->regex_font_attr(array('s'=>'col' ,'1'=>'\\1','2'=>'\\2'))", $txt);
			}

		}

		// Swop \n back to <br>
		//negram	$txt = preg_replace( "/\n/", "<br>", $txt );
		/*
	 * очистить от переносов строки внутри структуры таблиц
	 * чтоб не получилось такого:
	 * <table><br>
	 * <tr><br><td></td>
	 * </tr>
	 * </table>
	 */
		$txt = preg_replace('!<(table|tr)>\s*<(td|th|tr)>!', '<$1><$2>', $txt);
		$txt = preg_replace('!</(th|td)>\s*</(tr)>!', '</$1></$2>', $txt);
		$txt = preg_replace('!</(th|td|tr)>\s*<(\1)>!', '</$1><$2>', $txt);
		$txt = preg_replace('!</(tr)>\s*</(table)>!', '</$1></$2>', $txt);
		$txt = preg_replace('!<(table.*?)>\s*<(tr)>!', '<$1><$2>', $txt);

		if (version_compare(PHP_VERSION, '5.3.0', '<'))
		{
			$txt = nl2br($txt);
		} else
		{
			$txt = nl2br($txt, false);
		}

		// Unicode?
		if ($this->allow_unicode)
		{
			# &#174;
			$txt = preg_replace("/&amp;#([0-9]+);/s", "&#\\1;", $txt);
			# &#x00FF;
			$txt = preg_replace("/&amp;#x([0-9A-F]+);/s", "&#x\\1;", $txt);
			# &reg; &copy;
			$txt = preg_replace("/&amp;(\w+);/s", "&\\1;", $txt);
		}

		//+---------------------------------------------------------------------------------------------------
		// Parse smilies (disallow smilies in siggies, or we'll have to query the DB for each post
		// and each signature when viewing a topic, not something that we really want to do.
		//+---------------------------------------------------------------------------------------------------

		// Shaman * noHTML + Song * smile skin

		if ($in['SMILIES'] != 0 and $in['SIGNATURE'] == 0 and $ibforums->member['view_img'])
		{
			$txt = $this->parse_smiles($txt);
		}

		// **************************** moderators tags *************************************************************

		$txt = preg_replace("#\[gm\](.+?)\[/gm\]#ies", "\$this->regex_global_moderator_message('\\1',{$in['MID']})", $txt);

		$txt = preg_replace("#\[mm\](.+?)\[/mm\]#ies", "\$this->regex_moderator_message('\\1')", $txt);

		$txt = preg_replace_callback("#\[mod\](.+?)\[/mod\]#is", array(
		                                                              $this,
		                                                              'regex_mod_tag'
		                                                         ) /* "\$this->regex_mod_tag('\\1')"*/, $txt);

		$txt = preg_replace("#\[ex\](.+?)\[/ex\]#ies", "\$this->regex_exclaime_tag('\\1')", $txt);

		$txt = preg_replace("#\[pre\](.+?)\[/pre\]#ies", "\$this->regex_pre_tag('\\1')", $txt);

		// **************************** moderators tags *************************************************************

		// Song * time tag

		$txt = preg_replace("#\[mergetime\](\d+)\[/mergetime\]#ies", ($ibforums->vars['plg_offline_client'] or $ibforums->member['rss'])
			? "\$std->old_get_date( '\\1' )"
			: "\$std->get_date(     '\\1' )", $txt);

		//--------------------------------------
		// Auto parse URLs
		//--------------------------------------
		//It must to be after all text conversions to prevent interference with tags
		//However there are additional problems with already created <a> and <img> tags,
		//so first we skip urls after href= and src=
		//second, we need to avoid situations like <a href="url">url</a> can be added by [url] tag
		//so we add &shy; symbol to links created by [url] and check for it here
		$txt = preg_replace(
			//better, but [:alnum:] don't understand cyr characters sometimes.
			//'#(?<!\w|&shy;|href=.|src=.)((https?|news|s?ftp):\/\/([[:alpha:]][[:alnum:]-]*[[:alnum:]]\.?)+(:\d+)?([[:alnum:]_\.\-:\/\?\#\[\]@\!\$\&\'\(\)\*\+\,\;\=]|%[A-Z0-9]{2})*)#ie',
			'#(?<!\w|&shy;|href=.|src=.)((https?|news|s?ftp):\/\/[^\s\[\]\<\>\"]+)#ie',
			"\$this->regex_build_url(array('html' => '\\1', 'show' => '', 'st' => ''))",
			$txt
		);
		// Leprecon * return &shy; back to ''

		$txt = preg_replace("#&shy;#", "", $txt);

		return $txt;

	}

	/**************************************************/
	// regex_table: Table generation
	// (c) vot
	/**************************************************/

	function regex_table($txt = "")
	{
		//        	$txt = preg_replace( "#\[table\](.+?)\[/table\]#is", "<table class='post_table'>\\1</table>", $txt );
		$txt = preg_replace("#\[tr\](.+?)\[/tr\]#is", "<tr>\\1</tr>", $txt);
		$txt = preg_replace("#\[td\](.*?)\[/td\]#is", "<td>\\1</td>", $txt);
		$txt = preg_replace("#\[th\](.*?)\[/th\]#is", "<th>\\1</th>", $txt);
		return "<table class='post_table'>" . $txt . "</table>";
	}

	//---------------------------------------------------
	// Song * safe html text in code tag text, 03.11.2004

	function cut_code_tag_text($code, $syntax)
	{

		// cut \d\n at begin and all space characters at end of code tag text
		$code = rtrim(ltrim($code, "\n\r"));

		if ($syntax)
		{
			$txt = "[CODE=" . $syntax . "]" . $code . "[/CODE]";
		} else
		{
			$txt = "[CODE]" . $code . "[/CODE]";
		}

		$temp                   = $this->code_counter;
		$this->code_text[$temp] = $txt;
		$this->code_counter++;

		return "song_code_" . $temp . "#";

	}

	// Song * moderator messages check, 03.11.2004
	function mod_messages_check($message)
	{
		global $ibforums;

		if (!$message or !$ibforums->member['id'])
		{
			return $message;
		}
		if (!($ibforums->member['is_mod'] or $ibforums->member['g_is_supmod']))
		{
			return '';
		}

		return "[MM]" . $message . "[/MM]";

	}

	// Song * moderator messages check, 03.11.2004
	function global_mod_messages_check($message)
	{
		global $ibforums;

		if (!$ibforums->member['g_is_supmod'] or !$message)
		{
			return $message;
		}

		return "[GM]" . $message . "[/GM]";

	}

	/**********************************************************/
	// ********* Main Function Write to the Base **************
	// *** Parses raw text into smilies, HTML and iB CODE *****
	/**********************************************************/

	function convert($in = array(
		'TEXT'      => "",
		'SMILIES'   => 0,
		'CODE'      => 0,
		'SIGNATURE' => 0,
		'HTML'      => 0
	), $fid = "0")
	{
		global $ibforums, $std;

		$txt = $in['TEXT'];

		//--------------------------------------
		// Returns any errors as $this->error
		//--------------------------------------
		// Remove session id's from any post

		$txt = preg_replace("#(\?|&amp;|;|&)s=([0-9a-zA-Z]){32}(&amp;|;|&|$)?#e", "\$this->regex_bash_session('\\1', '\\3')", $txt);

		//--------------------------------------
		// convert <br> to \n
		//--------------------------------------

		$txt = preg_replace("/<br>|<br \/>/", "\n", $txt);

		//+---------------------------------------------------------------------------------------------------
		// Parse smilies (disallow smilies in siggies, or we'll have to query the DB for each post
		// and each signature when viewing a topic, not something that we really want to do.
		//+---------------------------------------------------------------------------------------------------

		if ($in['SMILIES'] != 0 and $in['SIGNATURE'] == 0)
		{
			if ($ibforums->vars['max_emos'] and $this->emoticon_count > $ibforums->vars['max_emos'])
			{
				$this->error = 'too_many_emoticons';
			}
		}

		// cut init
		$this->code_text    = array();
		$this->code_counter = 0;

		// cut code tag text from a post to an array
		$txt = preg_replace("#\[code\s*?(=\s*?(.*?)|)\s*\](.*?)\[/code\]#ies", "\$this->cut_code_tag_text('\\3', '\\2')", $txt);

		// **************************** moderators tags *************************************************************

		$txt = preg_replace("#\[mm\](.+?)\[/mm\]#ies", "\$this->mod_messages_check('\\1')", $txt);

		$txt = preg_replace("#\[gm\](.+?)\[/gm\]#ies", "\$this->global_mod_messages_check('\\1')", $txt);
		/*
        if ( $in['MOD_FLAG'] != TRUE )
	{
	    while(preg_match( "#\[(mod|ex|mm|gm)\](.+?)\[/(mod|ex|mm|gm)\]#is", $txt))
		$txt = preg_replace( "#\[(mod|ex|mm|gm)\](.+?)\[/(mod|ex|mm|gm)\]#is", '\\2', $txt);

	} else
	{
		$txt = preg_replace( "#\[(mod|ex)\](.+?)\[/(mod|ex)\]#ies", "\$this->regex_mod_tag_convert('\\1','\\2')", $txt);
	}
*/
		if (in_array($ibforums->member['mgroup'], $ibforums->vars['mm_groups'])
		    or $in['MOD_FLAG']
		)
		{
			$txt = preg_replace("#\[(mod|ex)\](.+?)\[/(mod|ex)\]#ies", "\$this->regex_mod_tag_convert('\\1','\\2')", $txt);
		} else
		{
			while (preg_match("#\[(mod|ex|mm|gm)\](.+?)\[/(mod|ex|mm|gm)\]#is", $txt))
			{
				$txt = preg_replace("#\[(mod|ex|mm|gm)\](.+?)\[/(mod|ex|mm|gm)\]#is", '\\2', $txt);
			}
		}

		// **************************** moderators tags *************************************************************

		// Song * cut code tag text before macro replacing, 03.11.2004

		// Song * macros replace

		$txt = $this->macro($txt, $fid);

		// Song * macros replace

		// Song * restore code tag text after html text parsing, 03.11.2004

		if ($this->code_counter)
		{
			foreach ($this->code_text as $idx => $code)
			{
				$txt = str_replace("song_code_{$idx}#", $code, $txt);
			}
		}

		// Song * restore code tag text after html text parsing, 03.11.2004

		$txt = $this->bad_words($txt);

		// vot: REMOVE/REPLACE disabled characters to spaces:

		$txt = str_replace("\r", "", $txt); // \015 = 13 = 0x0D
		$txt = preg_replace('#[\000-\010]#', " ", $txt);
		$txt = preg_replace('#[\013-\037]#', " ", $txt);

		$txt = trim(stripslashes($txt));

		return $txt;

	}

	// Song * mod tags to upper, 26.11.04

	function regex_mod_tag_convert($the_tag, $txt)
	{

		$the_tag = strtoupper($the_tag);
		return "[" . $the_tag . "]" . $txt . "[/" . $the_tag . "]";
	}

	// Song * mod tags to upper, 26.11.04

	// Song * macro hacks
	function macro($txt, $fid = "0")
	{

		// keep http text as is, except if it's internal forum link
		$txt = preg_replace_callback('#(^|\s)((http|https|news|ftp)://\w+[^\s\[\]]+)#i', array(
		                                                                                      $this,
		                                                                                      'regex_build_url_auto_parser_cut'
		                                                                                 ), $txt);

		// change url tags
		$txt = preg_replace("#\[url\](\S+?)\[/url\]#ie", "\$this->regex_build_url_auto_parser(array('html' => '\\1','show' => '\\1'))", $txt);

		$txt = preg_replace("#\[url\s*=\s*\&quot\;\s*(\S+?)\s*\&quot\;\s*\](.*?)\[\/url\]#ie", "\$this->regex_build_url_auto_parser(array('html' => '\\1','show' => '\\2'))", $txt);

		$txt = preg_replace("#\[url\s*=\s*(\S+?)\s*\](.*?)\[\/url\]#ie", "\$this->regex_build_url_auto_parser( array('html' => '\\1', 'show' => '\\2'))", $txt);

		// tags of forum search
		$txt = preg_replace("#\[sf\](.+?)\[/sf\]#ies", "\$this->regex_word_search('\\1','sf','word',$fid)", $txt);
		$txt = preg_replace("#\[sall\](.+?)\[/sall\]#ies", "\$this->regex_word_search('\\1','all','word')", $txt);
		$txt = preg_replace("#\[st\](.+?)\[/st\]#ies", "\$this->regex_word_search('\\1','sf','title',$fid)", $txt);
		$txt = preg_replace("#\[stall\](.+?)\[/stall\]#ies", "\$this->regex_word_search('\\1','all','title')", $txt);
		$txt = preg_replace("#\[sf\s*=\s*(\S+?)\s*\](.*?)\[\/sf\]#ie", "\$this->regex_word_search('\\2','sf','word','\\1')", $txt);
		$txt = preg_replace("#\[st\s*=\s*(\S+?)\s*\](.*?)\[\/st\]#ie", "\$this->regex_word_search('\\2','sf','title','\\1')", $txt);

		// tags for moderators
		$txt = preg_replace("/(\.[Пп]равил(а){0,1}(, п.\d+)*)/e", "\$this->wordreplacer('\\1','boardrules')", $txt);
		$txt = preg_replace("/(\.[Пп]оиск[а-я]{0,})/e", "\$this->wordreplacer('\\1','Search')", $txt);
		$txt = preg_replace("/(\.FAQ{0,})/e", "\$this->wordreplacer('\\1','faq',$fid)", $txt);

		// user tag
		$txt = preg_replace("#\[user\](.+?)\[/user\]#ies", "\$this->user_link('\\1')", $txt);

		return $txt;

	}

	// Song * parse rules and search

	function regex_word_search($word, $type, $search_in, $fid = "")
	{
		global $ibforums;

		$word = trim($word);

		if (!$word)
		{
			return;
		}

		$fid = intval($fid);

		$word_url = urlencode($word);

		if ($type == "sf")
		{
			$link = "ПОИСК: [URL={$ibforums->base_url}act=Search&CODE={$search_in}&f={$fid}&keywords={$word_url}]{$word}[/URL]";
		} else
		{
			$link = "ПОИСК: [URL={$ibforums->base_url}act=Search&CODE={$search_in}&keywords={$word_url}]{$word}[/URL]";
		}

		return $link;

	}

	function user_link($user_name = "")
	{
		global $ibforums;

		if (!$user_name)
		{
			return "";
		}

		$stmt = $ibforums->db->query("SELECT id FROM ibf_members WHERE LOWER(name)='" . strtolower(addslashes(trim($user_name))) . "'");
		if (!$stmt->rowCount())
		{
			return $user_name;
		}

		if ($row = $stmt->fetch())
		{
			return "[URL=" . $ibforums->base_url . "showuser=" . $row['id'] . "]" . $user_name . "[/URL]";

		} else
		{
			return $user_name;
		}

	}

	// Vot & Song * parse rules and search
	function wordreplacer($word, $type, $fid = "")
	{
		global $ibforums;

		$section = $word;
		$section = preg_replace("#\D#", "", $section);
		if ($section)
		{
			$section = "\#section" . $section;
		}
		$word = preg_replace("#^\.#", "", $word);

		if ($type == 'boardrules')
		{
			return "[URL={$ibforums->vars['gl_link']}{$section}][B][COLOR=RED]{$word}[/COLOR][/B][/URL]";
		} else

		{
			if ($type == 'faq' and $fid)
			{
				$stmt = $ibforums->db->query("SELECT parent_id FROM ibf_forums WHERE id='" . $fid . "'");

				$row = $stmt->fetch();

				if ($row['parent_id'] != -1)
				{
					$fid = $row['parent_id'];
				}

				$stmt = $ibforums->db->query("SELECT id FROM ibf_forums WHERE parent_id='" . $fid . "' and name LIKE '%FAQ%' LIMIT 1");

				if (!$stmt->rowCount())
				{
					return $word;
				} else
				{
					$row = $stmt->fetch();
					return "[URL={$ibforums->base_url}showforum=" . $row['id'] . "][B]{$word}[/B][/URL]";
				}

			} else
			{
				return "[URL={$ibforums->base_url}act={$type}][COLOR=RED]{$word}[/COLOR][/URL]";
			}
		}

	}

	//--------------------------------------------------------------
	// Post DB parse tags
	// ...................
	//--------------------------------------------------------------

	function post_db_parse($t = "", $use_html = 0)
	{
		global $ibforums;

		if ($use_html)
		{
			$t = preg_replace("#\[dohtml\](.+?)\[/dohtml\]#ies", "\$this->parse_html('\\1')", $t);
		} else
		{
			$t = preg_replace("#(\[dohtml\])(.+?)(\[/dohtml\])#ies", "\$this->my_strip_tags('\\2')", $t);
		}

		return $t;
	}

	//---------------------------------------------------------------
	// My strip-tags. Converts HTML entities back before strippin' em
	//---------------------------------------------------------------

	function my_strip_tags($t = "")
	{
		$t = str_replace('&gt;', '>', $t);
		$t = str_replace('&lt;', '<', $t);

		$t = strip_tags($t);

		// Make sure nothing naughty is left...

		$t = str_replace('<', '&lt;', $t);
		$t = str_replace('>', '&gt;', $t);

		return $t;
	}

	//--------------------------------------------------------------
	// Word wrap, wraps 'da word innit
	//--------------------------------------------------------------

	function my_wordwrap($t = "", $chrs = 0, $replace = "<br>")
	{
		if (!$t or $chrs < 1)
		{
			return $t;
		}

		$t = preg_replace("#([^\s<>'\"/\.\\-\?&\n\r\%\\]\\[]{" . $chrs . "})#iU", " \\1" . $replace, $t);

		return $t;

	}

	//--------------------------------------------------------------
	// parse_html
	// Converts the doHTML tag
	//--------------------------------------------------------------

	function parse_html($t = "", $do_br = 1)
	{
		if ($t == "")
		{
			return $t;
		}

		// Remove <br>s 'cos we know they can't
		// be user inputted, 'cos they are still
		// &lt;br&gt; at this point :)

		if ($do_br == 1)
		{
			$t = str_replace("<br>", "\n", $t);
			$t = str_replace("<br />", "\n", $t);
		}
		$t = str_replace("&#39;", "'", $t);
		$t = str_replace("&#33;", "!", $t);
		$t = str_replace("&#036;", "$", $t);
		$t = str_replace("&#124;", "|", $t);
		$t = str_replace("&amp;", "&", $t);
		$t = str_replace("&gt;", ">", $t);
		$t = str_replace("&lt;", "<", $t);
		$t = str_replace("&quot;", '"', $t);

		// Take a crack at parsing some of the nasties
		// NOTE: THIS IS NOT DESIGNED AS A FOOLPROOF METHOD
		// AND SHOULD NOT BE RELIED UPON!

		$t = preg_replace("/alert/i", "&#097;lert", $t);
		$t = preg_replace("/onmouseover/i", "&#111;nmouseover", $t);
		$t = preg_replace("/onclick/i", "&#111;nclick", $t);
		$t = preg_replace("/onload/i", "&#111;nload", $t);
		$t = preg_replace("/onsubmit/i", "&#111;nsubmit", $t);

		return $t;
	}

	//--------------------------------------------------------------
	// Badwords:
	// Swops naughty, naugty words and stuff
	//--------------------------------------------------------------

	function bad_words($text = "")
	{
		global $ibforums;

		if ($text == "")
		{
			return "";
		}

		if ($this->no_bad_words == 1)
		{
			return $text;
		}

		//--------------------------------

		if (!is_array($this->badwords))
		{
			$stmt = $ibforums->db->query("SELECT * from ibf_badwords");

			$this->badwords = array();

			if ($stmt->rowCount())
			{
				while ($r = $stmt->fetch())
				{
					$this->badwords[] = array(
						'type'    => stripslashes($r['type']),
						'swop'    => stripslashes($r['swop']),
						'm_exact' => $r['m_exact'],
					);
				}
			}
		}

		usort($this->badwords, array('PostParser', 'word_length_sort'));

		if (count($this->badwords) > 0)
		{

			foreach ($this->badwords as $idx => $r)
			{

				if ($r['swop'] == "")
				{
					$replace = '######';
				} else
				{
					$replace = $r['swop'];
				}

				//---------------------------

				$r['type'] = preg_quote($r['type'], "/");

				//---------------------------

				if ($r['m_exact'] == 1)
				{
					$text = preg_replace("/(^|\b)" . $r['type'] . "(\b|!|\?|\.|,|$)/i", "$replace", $text);
				} else
				{
					$text = preg_replace("/" . $r['type'] . "/i", "$replace", $text);
				}
			}

		}

		return $text;
	}

	/**************************************************/
	// unconvert:
	/**************************************************/

	function unconvert($txt = "", $code = 1, $html = 0)
	{

		if ($code == 1)
		{

			//----------------------------------
			// Align Hack by Farch (Unconvert)
			//----------------------------------
			$txt = preg_replace("#<div align=(right)>(.+?)</div>#", "\[r\]\\2\[/r\]", $txt);
			$txt = preg_replace("#<div align=(left)>(.+?)</div>#", "\[l\]\\2\[/l\]", $txt);
			$txt = preg_replace("#<div align=(center)>(.+?)</div>#", "\[c\]\\2\[/c\]", $txt);
			$txt = preg_replace("#\<hr>#", "\[hr\]", $txt);

			$txt = preg_replace("#<!--emo&(.+?)-->.+?<!--endemo-->#", "\\1", $txt);

			$txt = preg_replace("#<!--sql-->(.+?)<!--sql1-->(.+?)<!--sql2-->(.+?)<!--sql3-->#eis", "\$this->unconvert_sql(\"\\2\")", $txt);
			$txt = preg_replace("#<!--html-->(.+?)<!--html1-->(.+?)<!--html2-->(.+?)<!--html3-->#e", "\$this->unconvert_htm(\"\\2\")", $txt);

			$txt = preg_replace("#<!--Flash (.+?)-->.+?<!--End Flash-->#e", "\$this->unconvert_flash('\\1')", $txt);
			$txt = preg_replace("#<img src=[\"'](\S+?)['\"].+?" . ">#", "\[IMG\]\\1\[/IMG\]", $txt);

			$txt = preg_replace("#<a href=[\"']mailto:(.+?)['\"]>(.+?)</a>#", "\[EMAIL=\\1\]\\2\[/EMAIL\]", $txt);
			$txt = preg_replace("#<a href=[\"'](http://|https://|ftp://|news://)?(\S+?)['\"].+?" . ">(.+?)</a>#", "\[URL=\\1\\2\]\\3\[/URL\]", $txt);

			$txt = preg_replace("#<!--mod1-->(.+?)<!--emod1-->#", '[MOD]', $txt);
			$txt = preg_replace("#<!--mod2-->(.+?)<!--emod2-->#", '[/MOD]', $txt);

			$txt = preg_replace("#<!--excl1-->(.+?)<!--eexcl1-->#", '[EX]', $txt);
			$txt = preg_replace("#<!--excl2-->(.+?)<!--eexcl2-->#", '[/EX]', $txt);

			$txt = preg_replace("#<!--c1-->(.+?)<!--ec1-->#", '[CODE]', $txt);
			$txt = preg_replace("#<!--c2-->(.+?)<!--ec2-->#", '[/CODE]', $txt);

			$txt = preg_replace("#<!--QuoteBegin-->(.+?)<!--QuoteEBegin-->#", '[QUOTE]', $txt);
			$txt = preg_replace("#<!--QuoteBegin-{1,2}([^>]+?)\+([^>]+?)-->(.+?)<!--QuoteEBegin-->#", "[QUOTE=\\1,\\2]", $txt);
			$txt = preg_replace("#<!--QuoteBegin-{1,2}([^>]+?)\+-->(.+?)<!--QuoteEBegin-->#", "[QUOTE=\\1]", $txt);

			$txt = preg_replace("#<!--QuoteEnd-->(.+?)<!--QuoteEEnd-->#", '[/QUOTE]', $txt);

			$txt = preg_replace("#<i>(.+?)</i>#is", "\[i\]\\1\[/i\]", $txt);
			$txt = preg_replace("#<b>(.+?)</b>#is", "\[b\]\\1\[/b\]", $txt);
			$txt = preg_replace("#<s>(.+?)</s>#is", "\[s\]\\1\[/s\]", $txt);
			$txt = preg_replace("#<u>(.+?)</u>#is", "\[u\]\\1\[/u\]", $txt);

			$txt = preg_replace("#(\n){0,}<ul>#", "\\1\[LIST\]", $txt);
			$txt = preg_replace("#(\n){0,}<ol type='(a|A|i|I|1)'>#", "\\1\[LIST=\\2\]\n", $txt);
			$txt = preg_replace("#(\n){0,}<li>#", "\n\[*\]", $txt);
			$txt = preg_replace("#(\n){0,}</ul>(\n){0,}#", "\n\[/LIST\]\\2", $txt);
			$txt = preg_replace("#(\n){0,}</ol>(\n){0,}#", "\n\[/LIST\]\\2", $txt);

			$txt = preg_replace("#<!--me&(.+?)-->(.+?)<!--e--me-->#e", "\$this->unconvert_me('\\1', '\\2')", $txt);

			$txt = preg_replace("#<span style=['\"]font-size:(.+?)pt;line-height:100%['\"]>(.+?)</span>#e", "\$this->unconvert_size('\\1', '\\2')", $txt);

			while (preg_match("#<span style=['\"]color:(.+?)['\"]>(.+?)</span>#is", $txt))
			{
				$txt = preg_replace("#<span style=['\"]color:(.+?)['\"]>(.+?)</span>#is", "\[color=\\1\]\\2\[/color\]", $txt);
			}

			$txt = preg_replace("#<span style=['\"]font-family:(.+?)['\"]>(.+?)</span>#is", "\[font=\\1\]\\2\[/font\]", $txt);

			// Tidy up the end quote stuff

			$txt = preg_replace("#(\[/QUOTE\])\s*?<br>\s*#si", "\\1", $txt);

			$txt = preg_replace("#<!--EDIT\|.+?\|.+?-->#", "", $txt);

			$txt = str_replace("</li>", "", $txt);

			$txt = str_replace("&#153;", "(tm)", $txt);
		}

		if ($html == 1)
		{
			$txt = str_replace("&#39;", "'", $txt);
		}

		$txt = preg_replace("#<br>#", "\n", $txt);

		return trim(stripslashes($txt));
	}

	//+-----------------------------------------------------------------------------------------
	//+-----------------------------------------------------------------------------------------
	// UNCONVERT FUNCTIONS
	//+-----------------------------------------------------------------------------------------
	//+-----------------------------------------------------------------------------------------

	function unconvert_size($size = "", $text = "")
	{

		$size -= 7;

		return '[SIZE=' . $size . ']' . $text . '[/SIZE]';

	}

	function unconvert_flash($flash = "")
	{

		$f_arr = explode("+", $flash);

		return '[FLASH=' . $f_arr[0] . ',' . $f_arr[1] . ']' . $f_arr[2] . '[/FLASH]';

	}

	function unconvert_me($name = "", $text = "")
	{

		$text = preg_replace("#<span class='ME'><center>(.+?)</center></span>#", "\\1", $text);
		$text = preg_replace("#$name#", "", $text);

		return '[ME=' . $name . ']' . $text . '[/ME]';

	}

	function unconvert_sql($sql = "")
	{
		$sql = stripslashes($sql);

		while (preg_match("#<span style='.+?'>(.+?)</span>#is", $sql))
		{
			$sql = preg_replace("#<span style='.+?'>(.+?)</span>#is", "\\1", $sql);
		}

		$sql = preg_replace("#\s*$#", "", $sql);

		return '[SQL]' . $sql . '[/SQL]';

	}

	function unconvert_htm($html = "")
	{
		$html = stripslashes($html);

		while (preg_match("#<span style='.+?'>(.+?)</span>#is", $html))
		{
			$html = preg_replace("#<span style='.+?'>(.+?)</span>#is", "\\1", $html);
		}

		$html = preg_replace("#\s*$#", "", $html);

		return '[HTML]' . $html . '[/HTML]';

	}

	//+-----------------------------------------------------------------------------------------
	//+-----------------------------------------------------------------------------------------
	// CONVERT FUNCTIONS
	//+-----------------------------------------------------------------------------------------
	//+-----------------------------------------------------------------------------------------

	/**************************************************/
	// convert_emoticon:
	// replaces the text with the emoticon image
	/**************************************************/

	function convert_emoticon($code = "", $image = "")
	{
		global $ibforums;

		if (!$code or !$image)
		{
			return;
		}

		// Remove slashes added by preg_quote
		$code = stripslashes($code);

		$this->emoticon_count++;

		// Song * smile skin

		if (!$ibforums->member['id'])
		{
			$sskin = 'Main';
		} else
		{
			if (!$ibforums->member['view_img'] or $ibforums->member['sskin_id'] == 0)
			{
				return $code;
			}
			$sskin = $ibforums->member['sskin_name'];
		}

		//		return "<img src='".( ( $ibforums->vars['plg_offline_client'] or $ibforums->vars['pre_board_url'] ) ? $ibforums->vars['board_url']."/" : "" )."smiles/$sskin/$image' border='0' alt='$code'>";
		return "<img src='{$ibforums->vars['board_url']}/smiles/$sskin/$image' border='0' alt='$code'>";

		// /Song * smile skin

	}

	/**************************************************/
	// wrap style:
	// code and quote table HTML generator
	/**************************************************/

	function wrap_style($in = array())
	{
		global $ibforums;

		if (!isset($in['TYPE']))
		{
			$in['TYPE'] = 'class';
		}

		if (!isset($in['CSS']))
		{
			$in['CSS'] = $this->in_sig == 1
				? 'signature'
				: 'postcolor';
		}

		if (!isset($in['STYLE']))
		{
			$in['STYLE'] = 'QUOTE';
		}

		//-----------------------------
		// This returns two array elements:
		//  START: Contains the HTML code for the start wrapper
		//  END  : Contains the HTML code for the end wrapper
		//-----------------------------

		$possible_use = array(
			'CODE'  => array('CODE', ''),
			'QUOTE' => array('QUOTE', 'Цитата'),
			'SQL'   => array('CODE', 'SQL'),
			'HTML'  => array('CODE', 'HTML'),
			'PHP'   => array('CODE', 'PHP')

		);
		if ($possible_use[$in['STYLE']][1])
		{
			$label = "<b>{$possible_use[ $in['STYLE'] ][1]}</b>";

			// Song * quote with post link, 26.11.04

			if ($in['TID'] and $in['PID'] and
			                   !$ibforums->vars['plg_offline_client'] and
			                   !$this->cache_posts[$in['PID']]
			)
			{
				$label = "<a href='{$ibforums->base_url}showtopic={$in['TID']}&view=findpost&p={$in['PID']}'>{$label}</a>";
			}

			$label = "{$label} {$in['EXTRA']}";
		}

		$class = "";
		$style = '';
		if ($in['STYLE'] == "CODE")
		{

			if (isset($in['BCOLOR']))
			{
				$style .= 'background-color:' . self::color($in['BCOLOR']) . ';';
			}
			if (isset($in['FCOLOR']))
			{
				$style .= 'color:' . self::color($in['FCOLOR']) . ';';
			}

			// vot: BAD MESSAGE. Require language file

			if (!$ibforums->member['id'])
			{
				$extra .= " title='Подсветка синтаксиса доступна зарегистрированным участникам Форума.'";
				$class = " code_collapsed ";
			} else
			{
				$lines_count = $ibforums->member['syntax_lines_count'] !== NULL
					? $ibforums->member['syntax_lines_count']
					: 10;
				if ($lines_count != 0)
				{
					$lines_count *= 1.5;
					$style = sprintf("max-height: %sem;", strval(round($lines_count, 1)));
					$class .= " code_collapsed ";
				}
				$use_wrap           = $ibforums->member['syntax_use_wrap'] !== NULL
					? $ibforums->member['syntax_use_wrap']
					: false;
				$use_line_colouring = $ibforums->member['syntax_use_line_colouring'] !== NULL
					? $ibforums->member['syntax_use_line_colouring']
					: true;
				$use_line_numbering = $ibforums->member['syntax_use_line_numbering'] !== NULL
					? $ibforums->member['syntax_use_line_numbering']
					: false;
				if ($use_wrap)
				{
					$class .= " code_wrap ";
				}
				if ($use_line_colouring)
				{
					$class .= " line_coloured_code ";
				}
				if ($use_line_numbering)
				{
					$class .= " code_numbered ";
				}
			}
			$code_id = $in['CODE_ID'];
			$extra .= " style='{$style}'";
			if (!$this->rss_mode)
			{
				$label = '<span onclick=\'return syntax_collapse(this,' . $code_id . ');\' title=\'' . $ibforums->lang['code_collapse_button'] . '\'>';
				$label .= '<span id=\'code_collapse_on_' . $code_id . '\' ' . ($lines_count == 0
					? 'style=\'display:none\''
					: '') . '><{CODE_COLLAPSE_ON}></span>';
				$label .= '<span id=\'code_collapse_off_' . $code_id . '\' ' . ($lines_count == 0
					? ''
					: 'style=\'display:none\'') . '><{CODE_COLLAPSE_OFF}>';
				$label .= '</span></span>';

				$label .= '<span onclick=\'return syntax_wrap(this,' . $code_id . ');\' title=\'' . $ibforums->lang['code_word_wrap_button'] . '\'>';
				$label .= "<span id='code_wrap_on_$code_id' " . ($use_wrap
					? ''
					: "style='display:none'") . "><{CODE_WRAP_ON}></span>";
				$label .= "<span id='code_wrap_off_$code_id' " . ($use_wrap
					? "style='display:none'"
					: '') . "><{CODE_WRAP_OFF}>";
				$label .= '</span></span>';

				if ($ibforums->member['id'] && $ibforums->member['syntax'] != 'none')
				{
					$label .= '<span onclick=\'return syntax_numbering(this,' . $code_id . ');\' title=\'' . $ibforums->lang['code_line_numbers_button'] . '\'>';
					$label .= "<span id='code_numbering_on_$code_id'' " . ($use_line_numbering
						? ''
						: "style='display:none'") . "><{CODE_NUMBERING_ON}></span>";
					$label .= "<span id='code_numbering_off_$code_id'' " . ($use_line_numbering
						? "style='display:none'"
						: '') . "><{CODE_NUMBERING_OFF}>";
					$label .= '</span></span>';
				}
			}
			$pre_div_class = 'pre_code';
			$show_controls = $ibforums->member['syntax_show_controls'] !== NULL
				? $ibforums->member['syntax_show_controls']
				: 'yes';
			if ($show_controls != 'yes')
			{
				$pre_div_class .= ' no_controls_pre_code';
			}
			$label = "<span class='$pre_div_class'>$label</span>";
		}

		return array(
			'START' => "<div class='{$pre_div_class}'>{$label}<div class='" . strtolower($possible_use[$in['STYLE']][0]) . " $class'{$extra}>",
			'END'   => "</div></div>"
		);
	}

	/**************************************************/
	// regex_list: List generation
	//
	/**************************************************/

	function regex_list($txt = "", $type = "")
	{
		if ($txt == "")
		{
			return;
		}

		//$txt = str_replace( "\n", "", str_replace( "\r\n", "\n", $txt ) );

		if ($type == "")
		{
			// Unordered list.

			return "<ul style='margin-top:0; margin-bottom:0'>" . $this->regex_list_item($txt) . "</ul>";
		} else
		{
			return "<ol type='$type'>" . $this->regex_list_item($txt) . "</ol>";
		}
	}

	function regex_list_item($txt)
	{
		$txt = preg_replace("#\[\*\]#", "</li><li>", trim($txt));

		$txt = preg_replace("#^</?li>#", "", $txt);

		return str_replace("\n</li>", "</li>", $txt . "</li>");
	}

	/**************************************************/
	// regex_html_tag: HTML syntax highlighting
	//
	/**************************************************/

	function regex_html_tag($html = "")
	{

		if ($html == "")
		{
			return;
		}

		// Ensure that spacing is preserved

		// Too many embedded code/quote/html/sql tags can crash Opera and Moz

		if (preg_match("/\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\]/i", $html))
		{
			return $default;
		}

		//$html = preg_replace( "#\s{2}#", " &nbsp;", $html );

		// Knock off any preceeding newlines (which have
		// since been converted into <br>)

		//$html = nl2br($html);

		// Take a stab at removing most of the common
		// smilie characters.

		$html = preg_replace("#:#", "&#58;", $html);
		$html = preg_replace("#\[#", "&#91;", $html);
		$html = preg_replace("#\]#", "&#93;", $html);
		$html = preg_replace("#\)#", "&#41;", $html);
		$html = preg_replace("#\(#", "&#40;", $html);

		$html = preg_replace("/^<br>/", "", $html);

		$html = preg_replace("#&lt;([^&<>]+)&gt;#", "&lt;<span style='color:blue'>\\1</span>&gt;", $html); //Matches <tag>
		$html = preg_replace("#&lt;([^&<>]+)=#", "&lt;<span style='color:blue'>\\1</span>=", $html); //Matches <tag
		$html = preg_replace("#&lt;/([^&]+)&gt;#", "&lt;/<span style='color:blue'>\\1</span>&gt;", $html); //Matches </tag>
		$html = preg_replace("!=(&quot;|&#39;)(.+?)?(&quot;|&#39;)(\s|&gt;)!", "=\\1<span style='color:orange'>\\2</span>\\3\\4", $html); //Matches ='this'
		$html = preg_replace("!&#60;&#33;--(.+?)--&#62;!", "&lt;&#33;<span style='color:red'>--\\1--</span>&gt;", $html);

		$wrap = $this->wrap_style(array('STYLE' => 'HTML'));

		return $wrap['START'] . $html . $wrap['END'];
	}

	/**************************************************/
	// regex_sql_tag: SQL syntax highlighting
	//
	/**************************************************/

	function regex_sql_tag($sql = "")
	{

		if ($sql == "")
		{
			return;
		}

		// Too many embedded code/quote/html/sql tags can crash Opera and Moz

		if (preg_match("/\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\].+?\[(quote|code|html|sql)\]/i", $sql))
		{
			return $default;
		}

		// Knock off any preceeding newlines (which have
		// since been converted into <br>)

		$sql = preg_replace("/^<br>/", "", $sql);
		$sql = preg_replace("/^\s+/", "", $sql);

		// Make certain regex work..

		if (!preg_match("/\s+$/", $sql))
		{
			$sql = $sql . ' ';
		}

		$sql = preg_replace("#(=|\+|\-|&gt;|&lt;|~|==|\!=|LIKE|NOT LIKE|REGEXP)#i", "<span style='color:orange'>\\1</span>", $sql);
		$sql = preg_replace("#(MAX|AVG|SUM|COUNT|MIN)\(#i", "<span style='color:blue'>\\1</span>(", $sql);
		$sql = preg_replace("!(&quot;|&#39;|&#039;)(.+?)(&quot;|&#39;|&#039;)!i", "<span style='color:red'>\\1\\2\\3</span>", $sql);
		$sql = preg_replace("#\s{1,}(AND|OR)\s{1,}#i", " <span style='color:blue'>\\1</span> ", $sql);
		$sql = preg_replace("#(LEFT|JOIN|WHERE|MODIFY|CHANGE|AS|DISTINCT|IN|ASC|DESC|ORDER BY)\s{1,}#i", "<span style='color:green'>\\1</span> ", $sql);
		$sql = preg_replace("#LIMIT\s*(\d+)\s*,\s*(\d+)#i", "<span style='color:green'>LIMIT</span> <span style='color:orange'>\\1, \\2</span>", $sql);
		$sql = preg_replace("#(FROM|INTO)\s{1,}(\S+?)\s{1,}#i", "<span style='color:green'>\\1</span> <span style='color:orange'>\\2</span> ", $sql);
		$sql = preg_replace("#(SELECT|INSERT|UPDATE|DELETE|ALTER TABLE|DROP)#i", "<span style='color:blue;font-weight:bold'>\\1</span>", $sql);

		$html = $this->wrap_style(array('STYLE' => 'SQL'));

		return $html['START'] . $sql . $html['END'];
	}

	/**************************************************/
	// regex_mod_tag: Builds this code tag HTML
	//
	/**************************************************/

	function regex_mod_tag($txt = "")
	{
		global $ibforums;

		if (!$txt)
		{
			return;
		}
		$txt = $txt[1];
		// Take a stab at removing most of the common
		// smilie characters.

		$txt = preg_replace("#&lt;#", "&#60;", $txt);
		$txt = preg_replace("#&gt;#", "&#62;", $txt);
		$txt = preg_replace("#&quot;#", "&#34;", $txt);
		$txt = preg_replace("#:#", "&#58;", $txt);
		$txt = preg_replace("#\[#", "&#91;", $txt);
		$txt = preg_replace("#\]#", "&#93;", $txt);
		$txt = preg_replace("#\)#", "&#41;", $txt);
		$txt = preg_replace("#\(#", "&#40;", $txt);
		$txt = preg_replace("#\s{1};#", "&#59;", $txt);

		// Ensure that spacing is preserved

		$txt = preg_replace("#\s{2}#", "&nbsp; ", $txt);

		$html = "</div><BR><TABLE {$ibforums->skin['white_background']} style='border:2px solid blue;'><TD align=middle bgColor=#6060ff valign=center width=1%><span style='color:#ffffff; font-family: Times; font-size:4em' ><B>М</B></span></TD><TD width=3><BR></TD><TD valign=top>{$txt}</TD></TABLE><div class='postcolor'>";

		return $html;
	}

	/**************************************************/
	// regex_exclaime_tag: Builds this code tag HTML
	//
	/**************************************************/

	function regex_exclaime_tag($txt = "")
	{
		global $ibforums;

		if (!$txt)
		{
			return;
		}

		// Take a stab at removing most of the common
		// smilie characters.

		$txt = preg_replace("#&lt;#", "&#60;", $txt);
		$txt = preg_replace("#&gt;#", "&#62;", $txt);
		$txt = preg_replace("#&quot;#", "&#34;", $txt);
		$txt = preg_replace("#:#", "&#58;", $txt);
		$txt = preg_replace("#\[#", "&#91;", $txt);
		$txt = preg_replace("#\]#", "&#93;", $txt);
		$txt = preg_replace("#\)#", "&#41;", $txt);
		$txt = preg_replace("#\(#", "&#40;", $txt);
		$txt = preg_replace("#\s{1};#", "&#59;", $txt);

		// Ensure that spacing is preserved

		$txt = preg_replace("#\s{2}#", "&nbsp; ", $txt);

		$html = "</div><BR><TABLE {$ibforums->skin['white_background']} style='border:2px solid red;'><TD align=middle bgColor=#ff6060 valign=center width=1%><span style='color:#ffffff; font-family: Times; font-size:4em'><B>&nbsp;!&nbsp;</B></span></TD><TD width=3><BR></TD><TD valign=top>{$txt}</TD></TABLE><div class='postcolor'>";

		return $html;
	}

	/****************************************************************************************************/
	// regex_parse_quotes: Builds this quote tag HTML
	// [QUOTE] .. [/QUOTE] - allows for embedded quotes
	/**************************************************/

	function regex_parse_quotes($matches)
	{
		$the_txt = $matches[0];

		if (!$the_txt)
		{
			return;
		}

		$txt = $the_txt;

		// Too many embedded code/quote/html/sql tags can crash Opera and Moz

		$this->quote_html = $this->wrap_style(array('STYLE' => 'QUOTE'));

		$txt = preg_replace_callback('#\[quote\]#i', [$this,'regex_simple_quote_tag'], $txt);

		// Song * quote with post link, 26.11.04

		// for old date quote format: Wes,22.03.04, 22:24
		$txt = preg_replace_callback('#\[quote\s*=([^\],]+?),(\d{2,4}\.\d{2}\.\d{2},\s*\d{2}:\d{2}(?:\d{2})?)(?:,(\d+))?\]#i', [$this,'regex_quote_tag'], $txt);

		// for new date quote format: negram,1364666527,2642750
		$txt = preg_replace_callback('#\[quote\s*=([^\]]+?),(\d+?),(\d+?)\]#i', [$this,'regex_quote_tag'], $txt);

		// Song * quote with post link, 26.11.04
		$txt = preg_replace_callback('#\[quote\s*=([^\]]+?),(\d+?)\]#i', [$this, 'regex_quote_tag'], $txt);
		
		$txt = preg_replace_callback('#\[quote\s*=([^\]]+?)\]#i', [$this,'regex_quote_tag'], $txt);
		
		$txt = preg_replace_callback('#\[/quote\]#i', [$this, 'regex_close_quote'], $txt);

		if ($this->quote_open == $this->quote_closed and !$this->quote_error)
		{
			$txt = preg_replace("#(<!--QuoteEBegin-->.+?<!--QuoteEnd-->)#es", "\$this->regex_preserve_spacing('\\1')", trim($txt));

			return $txt;
		}

		return $the_txt;
	}

	/**************************************************/
	// regex_preserve_spacing: keeps double spaces
	// without CSS killing <pre> tags
	/**************************************************/

	function regex_preserve_spacing($txt = "")
	{
		$txt = preg_replace("#\s{2}#", "&nbsp; ", trim($txt));
		return $txt;
	}

	/**************************************************/
	// regex_simple_quote_tag: Builds this quote tag HTML
	// [QUOTE] .. [/QUOTE]
	/**************************************************/

	function regex_simple_quote_tag()
	{
		$this->quote_open++;

		//		return "<!--QuoteBegin-->{$this->quote_html['START']}<!--QuoteEBegin-->";
		return $this->quote_html['START'];

	}

	/**************************************************/
	// regex_close_quote: closes a quote tag
	//
	/**************************************************/

	function regex_close_quote()
	{

		if (!$this->quote_open)
		{
			$this->quote_error++;
			return;
		}

		$this->quote_closed++;

		return $this->quote_html['END'];
	}

	/**************************************************/
	// regex_quote_tag: Builds this quote tag HTML
	// [QUOTE=Matthew,14 February 2002]
	/**************************************************/

	function regex_quote_tag($matches)
	{
		global $ibforums, $std;
			
		$name = $matches[1];
		$date = $matches[2];
		$pid  = intval($matches[3]);
		
		$tid = $this->topic_id;

		$name = str_replace("+", "&#043;", $name);
		$name = str_replace("-", "&#045;", $name);

		$this->quote_open++;

		if (!$date)
		{
			$html = $this->wrap_style(array(
			                               'STYLE' => "QUOTE",
			                               'EXTRA' => "($name)",
			                               'PID'   => $pid,
			                               'TID'   => $tid
			                          ));

		} else
		{
			// with point - old format of date
			// without point - new format of date in UNIX time

			if (strpos($date, ".") === FALSE)
			{
				$date = ($ibforums->vars['plg_offline_client'] or $ibforums->member['rss'])
					? $std->old_get_date($date)
					: $std->get_date($date, -1);
			}

			$html = $this->wrap_style(array(
			                               'STYLE' => "QUOTE",
			                               'EXTRA' => "($name &#064; $date)",
			                               'PID'   => $pid,
			                               'TID'   => $tid
			                          ));
		}

		return $html['START'];

	}

	/****************************************************************************************************/
	// regex_check_flash: Checks, and builds the <object>
	// html.
	/**************************************************/

	function regex_check_flash($width = "", $height = "", $url = "")
	{
		global $ibforums;

		$default = "\[flash=$width,$height\]$url\[/flash\]";

		if (!$ibforums->vars['allow_flash'])
		{
			return $default;
		}

		if ($width > $ibforums->vars['max_w_flash'])
		{
			$this->error = 'flash_too_big';
			return $default;
		}

		if ($height > $ibforums->vars['max_h_flash'])
		{
			$this->error = 'flash_too_big';
			return $default;
		}

		if (!preg_match("/^http:\/\/(\S+)\.swf$/i", $url))
		{
			$this->error = 'flash_url';
			return $default;
		}

		return "<OBJECT CLASSID='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' WIDTH=$width HEIGHT=$height><PARAM NAME=MOVIE VALUE=$url><PARAM NAME=PLAY VALUE=TRUE><PARAM NAME=LOOP VALUE=TRUE><PARAM NAME=QUALITY VALUE=HIGH><EMBED SRC=$url WIDTH=$width HEIGHT=$height PLAY=TRUE LOOP=TRUE QUALITY=HIGH></EMBED></OBJECT>";
	}

	/**************************************************/
	// regex_check_image: Checks, and builds the <img>
	// html.
	/**************************************************/

	function regex_check_image($url = "", $alt = "")
	{
		global $ibforums, $std;

		if (!$url)
		{
			return;
		}

		$url = trim($url);

		$default = "[img]" . $url . "[/img]";

		++$this->image_count;

		// Make sure we've not overriden the set image # limit

		if ($ibforums->vars['max_images'])
		{
			if ($this->image_count > $ibforums->vars['max_images'])
			{
				$this->error = 'too_many_img';
				return $default;
			}
		}

		// Are they attempting to post a dynamic image, or JS?

		if ($ibforums->vars['allow_dynamic_img'] != 1)
		{
			if (preg_match("/[?&;]/", $url))
			{
				$this->error = 'no_dynamic';
				return $default;
			}
			if (preg_match("/javascript(\:|\s)/i", $url))
			{
				$this->error = 'no_dynamic';
				return $default;
			}
		}

		// Is the img extension allowed to be posted?

		if ($ibforums->vars['img_ext'])
		{
			$extension = preg_replace("#^.*\.(\S+)$#", "\\1", $url);

			$extension = strtolower($extension);

			if ((!$extension) OR (preg_match("#/#", $extension)))
			{
				$this->error = 'invalid_ext';
				return $default;
			}

			$ibforums->vars['img_ext'] = strtolower($ibforums->vars['img_ext']);

			if (!preg_match("/" . preg_quote($extension, '/') . "(\||$)/", $ibforums->vars['img_ext']))
			{
				$this->error = 'invalid_ext';
				return $default;
			}
		}

		// Is it a legitimate image?

		if (!preg_match("/^(http|https|ftp):\/\//i", $url))
		{
			$this->error = 'no_dynamic';
			return $default;
		}

		// If we are still here....

		$url = str_replace(" ", "%20", $url);

		if (!$alt)
		{
			$alt = "user posted image";
		} else
		{
			$alt   = $std->clean_value($alt);
			$alt   = $std->remove_tags($alt);
			$title = " title='$alt'";
		}
		return "<img src='$url' border='0' alt='$alt'$title>";

	}

	/**************************************************/
	// regex_font_attr:
	// Returns a string for an /e regexp based on the input
	/**************************************************/

	function regex_font_attr($IN)
	{

		if (!is_array($IN))
		{
			return "";
		}

		// Trim out stoopid 1337 stuff
		// [color=black;font-size:500pt;border:orange 50in solid;]hehe[/color]

		if (preg_match("/;/", $IN['1']))
		{
			$attr = explode(";", $IN['1']);

			$IN['1'] = $attr[0];
		}

		$IN['1'] = preg_replace("/[&\(\)\.\%]/", "", $IN['1']);

		if ($IN['s'] == 'size')
		{
			$IN['1'] = $IN['1'] + 7;

			if ($IN['1'] > 30)
			{
				$IN['1'] = 30;
			}

			return "<span style='font-size:" . $IN['1'] . "pt;line-height:100%'>" . $IN['2'] . "</span>";

		} elseif ($IN['s'] == 'col')
		{
			$IN[1] = preg_replace("/[^\d\w\#\s]/s", "", $IN[1]);

			// Song * Mastilior skin patch for red color, 30.04.05

			if ($IN['1'] == "red" or $IN['1'] == "RED")
			{
				return "<span class='movedprefix'>{$IN['2']}</span>";
			} elseif (preg_match('!^([0-9a-f]{6}|[0-9a-f]{3})$!i', $IN['1']))
			{
				return "<span style='color:#{$IN['1']}'>{$IN['2']}</span>";
			} else
			{
				return "<span style='color:{$IN['1']}'>{$IN['2']}</span>";
			}

			// Song * Mastilior skin patch for red color, 30.04.05

		} elseif ($IN['s'] == 'font')
		{
			$IN['1'] = preg_replace("/[^\d\w\#\-\_\s]/s", "", $IN['1']);

			return "<span style='font-family:" . $IN['1'] . "'>" . $IN['2'] . "</span>";
		}
	}

	/**************************************************/
	// regex_build_url: Checks, and builds the a href
	// html
	/**************************************************/

	function regex_build_url($url = array())
	{
		$url['html'] = str_replace("/TEST/", "/", $url['html']);

		//add 'end' key
		if(!isset($url['end']))
		{
			$url['end'] = '';
		}
		$matches = null;
		//look for ascii symbols and html version of forbidden ones (<>"{}~) and break url
		//on the first of them.
		if(preg_match('/&#\d+;|&quot;|&lt;|&gt;/', $url['html'], $matches, PREG_OFFSET_CAPTURE))
		{
			$pos = $matches[0][1];
			$url['end'] = substr($url['html'], $pos) . $url['end'];
			$url['html'] = substr($url['html'], 0, $pos);
		}
		if (!trim($url['show']))
		{
			$url['show'] = $url['html'];
		}

		$skip_it = 0;

		// Make sure the last character isn't punctuation.. if it is, remove it and add it to the
		// end array

		if (preg_match("/([\.,\?]|&#33;)$/", $url['html'], $match))
		{
			$url['end'] = $match[1] . $url['end'];
			$url['html'] = preg_replace("/([\.,\?]|&#33;)$/", "", $url['html']);
			$url['show'] = preg_replace("/([\.,\?]|&#33;)$/", "", $url['show']);
		}

		// Make sure it's not being used in a closing code/quote/html or sql block

		if (preg_match("/\[\/(html|quote|code|sql)/i", $url['html']))
		{
			return $url['html'];
		}

		// clean up the ampersands
		$url['html'] = preg_replace("/&amp;/", "&", $url['html']);

		// Make sure we don't have a JS link
		$url['html'] = preg_replace("/javascript:/i", "java script&#58; ", $url['html']);

		// Do we have http:// at the front?

		if (!preg_match("#^(http|news|https|ftp|aim)://#", $url['html']))
		{
			$url['html'] = 'http://' . $url['html'];
		}

		//-------------------------
		// Tidy up the viewable URL
		//-------------------------

		if (preg_match("/^<img src/i", $url['show']))
		{
			$skip_it = 1;
		}

		$url['show'] = preg_replace("/&amp;/", "&", $url['show']);
		$url['show'] = preg_replace("/javascript:/i", "javascript&#58; ", $url['show']);

		if ((strlen($url['show']) - 58) < 3)
		{
			$skip_it = 1;
		}

		// Make sure it's a "proper" url

		if (!preg_match("/^(http|ftp|https|news):\/\//i", $url['show']))
		{
			$skip_it = 1;
		}

		$show = $url['show'];

		if ($skip_it != 1)
		{
			$stripped = preg_replace("#^(http|ftp|https|news)://(\S+)$#i", "\\2", $url['show']);
			$uri_type = preg_replace("#^(http|ftp|https|news)://(\S+)$#i", "\\1", $url['show']);

			$show = $uri_type . '://' . substr($stripped, 0, 35) . '...' . substr($stripped, -15);
		}

		return $url['st'] . "<a href='" . $url['html'] . "' target='_blank'>&shy;" . $show . "</a>" . $url['end'];

	}

	function regex_bash_session($start_tok, $end_tok)
	{
		// Bug fix :D
		// Case 1: index.php?s=0000        :: Return nothing (parses: index.php)
		// Case 2: index.php?s=0000&this=1 :: Return ?       (parses: index.php?this=1)
		// Case 3: index.php?this=1&s=0000 :: Return nothing (parses: index.php?this=1)
		// Case 4: index.php?t=1&s=00&y=2  :: Return &       (parses: index.php?t=1&y=2)
		// Thanks to LavaSoft for spotting this one.

		$start_tok = str_replace('&amp;', '&', $start_tok);
		$end_tok   = str_replace('&amp;', '&', $end_tok);

		//1:
		if ($start_tok == '?' and $end_tok == '')
		{
			return "";
		} //2:
		else {
			if ($start_tok == '?' and $end_tok == '&')
			{
				return '?';
			} //3:
			else
			{
				if ($start_tok == '&' and $end_tok == '')
				{
					return "";
				} else
				{
					if ($start_tok == '&' and $end_tok == '&')
					{
						return "&";
					} else
					{
						return $start_tok . $end_tok;
					}
				}
			}
		}

	}

	function regex_build_url_auto_parser($url = array())
	{
		$ibforums = Ibf::app();

		$skip_it = 0;

		// Make sure the last character isn't punctuation.. if it is, remove it and add it to the
		// end array

		if (preg_match("/([\.,\?]|&#33;)$/", $url['html'], $match))
		{
			$url['end'] .= $match[1];
			$url['html'] = preg_replace("/([\.,\?]|&#33;)$/", "", $url['html']);
			$url['show'] = preg_replace("/([\.,\?]|&#33;)$/", "", $url['show']);
		}

		// Make sure it's not being used in a closing code/quote/html or sql block

		if (preg_match("/\[\/(html|quote|code|sql)/i", $url['html']))
		{
			return $url['html'];
		}

		// clean up the ampersands
		$url['html'] = preg_replace("/&amp;/", "&", $url['html']);

		// Make sure we don't have a JS link
		$url['html'] = preg_replace("/javascript:/i", "java script&#58; ", $url['html']);

		// Do we have http:// at the front?

		if (!preg_match("#^(http|news|https|ftp|aim)://#", $url['html']))
		{
			$url['html'] = 'http://' . $url['html'];
		}

		//-------------------------
		// Tidy up the viewable URL
		//-------------------------

		if (preg_match("/^<img src/i", $url['show']))
		{
			$skip_it = 1;
		}

		$url['show'] = preg_replace("/&amp;/", "&", $url['show']);
		$url['show'] = preg_replace("/javascript:/i", "javascript&#58; ", $url['show']);

		if ((strlen($url['show']) - 58) < 3)
		{
			$skip_it = 1;
		}

		// Make sure it's a "proper" url

		if (!preg_match("/^(http|ftp|https|news):\/\//i", $url['show']))
		{
			$skip_it = 1;
		}

		$show = $url['show'];

		//url auto parser begin

		//vot	if ( stristr($show, "forum.sources.ru") )
		if (stristr($show, $ibforums->vars['board_url']))
		{
			if (preg_match("/showtopic=(\d+)/", $show, $find) || preg_match("/&t=(\d+)/", $show, $find))
			{
				$stmt   = $ibforums->db->query("SELECT title FROM ibf_topics WHERE tid=" . intval($find[1]));
				$record = $stmt->fetch();

				if (preg_match("~p=(\d+)~", $show, $find))
				{
					$record['title'] = $record['title'] . " (сообщение #" . $find[1] . ")";
				}

				$show = $record['title'];
			}

			if (preg_match("/showforum=(\d+)/", $show, $find))
			{
				$stmt   = $ibforums->db->query("SELECT name FROM ibf_forums WHERE id=" . intval($find[1]));
				$record = $stmt->fetch();

				$show = $record['name'];
			}

			if (preg_match("/showuser=(\d+)/", $show, $find))
			{
				$stmt   = $ibforums->db->query("SELECT name FROM ibf_members WHERE id=" . intval($find[1]));
				$record = $stmt->fetch();

				$show = $record['name'];
			}
		} else
		{
			if ($skip_it != 1)
			{
				$stripped = preg_replace("#^(http|ftp|https|news)://(\S+)$#i", "\\2", $url['show']);
				$uri_type = preg_replace("#^(http|ftp|https|news)://(\S+)$#i", "\\1", $url['show']);

				$show = $uri_type . '://' . substr($stripped, 0, 35) . '...' . substr($stripped, -15);
			}
		}

		return $url['st'] . "[URL=" . $url['html'] . "]" . $show . "[/URL]" . $url['end'];

	}

	function regex_build_url_auto_parser_cut(array $url)
	{
		global $ibforums;
		// "\$this->regex_build_url_auto_parser_cut( array('html' => '\\2', 'show' => '\\2', 'st' => '\\1'))"
		$url     = array('html' => $url[2], 'show' => $url[2], 'st' => $url[1]);
		$skip_it = 0;

		// Make sure the last character isn't punctuation.. if it is, remove it and add it to the
		// end array

		if (preg_match("/([\.,\?]|&#33;)$/", $url['html'], $match))
		{
			$url['end'] .= $match[1];
			$url['html'] = preg_replace("/([\.,\?]|&#33;)$/", "", $url['html']);
			$url['show'] = preg_replace("/([\.,\?]|&#33;)$/", "", $url['show']);
		}

		// Make sure it's not being used in a closing code/quote/html or sql block

		if (preg_match("/\[\/(html|quote|code|sql)/i", $url['html']))
		{
			return $url['html'];
		}

		// clean up the ampersands
		$url['html'] = preg_replace("/&amp;/", "&", $url['html']);

		// Make sure we don't have a JS link
		$url['html'] = preg_replace("/javascript:/i", "java script&#58; ", $url['html']);

		// Do we have http:// at the front?

		if (!preg_match("#^(http|news|https|ftp|aim)://#", $url['html']))
		{
			$url['html'] = 'http://' . $url['html'];
		}

		//-------------------------
		// Tidy up the viewable URL
		//-------------------------

		if (preg_match("/^<img src/i", $url['show']))
		{
			$skip_it = 1;
		}

		$url['show'] = preg_replace("/&amp;/", "&", $url['show']);
		$url['show'] = preg_replace("/javascript:/i", "javascript&#58; ", $url['show']);

		if ((strlen($url['show']) - 58) < 3)
		{
			$skip_it = 1;
		}

		// Make sure it's a "proper" url

		if (!preg_match("/^(http|ftp|https|news):\/\//i", $url['show']))
		{
			$skip_it = 1;
		}

		$show = $url['show'];

		//url auto parser begin

		//vot	if ( stristr($show, "forum.sources.ru") )
		if (stristr($show, $ibforums->vars['board_url']))
		{
			if (preg_match("/showtopic=(\d+)/", $show, $find) || preg_match("/&t=(\d+)/", $show, $find))
			{
				$stmt = $ibforums->db->query("SELECT title FROM ibf_topics WHERE club=0 and tid='" . intval($find[1]) . "'");
				if ($record = $stmt->fetch())
				{
					if (preg_match("~p=(\d+)~", $show, $find))
					{
						$record['title'] = $record['title'] . " (сообщение #" . $find[1] . ")";
					}

					$show = $record['title'];
				}
			}

			if (preg_match("/showforum=(\d+)/", $show, $find))
			{
				$stmt = $ibforums->db->query("SELECT name FROM ibf_forums WHERE id='" . intval($find[1]) . "'");
				if ($record = $stmt->fetch())
				{
					$show = $record['name'];
				}
			}

			if (preg_match("/showuser=(\d+)/", $show, $find))
			{
				$stmt = $ibforums->db->query("SELECT name FROM ibf_members WHERE id='" . intval($find[1]) . "'");
				if ($record = $stmt->fetch())
				{
					$show = $record['name'];
				}
			}

			return $url['st'] . "[URL=" . $url['html'] . "]" . $show . "[/URL]" . $url['end'];

		} else
		{
			return $url['st'] . $url['html'] . $url['end'];
		}

	}

}
