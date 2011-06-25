<?php

class AuthMethodOpenId extends AuthBasic {
	
	private $url;
	
	function __construct() {
		$this->requireLib();
	}
	
	public function checkInput() {
		global $DB, $ibforums, $std, $print, $sess;

		$len_u = $std->txt_stripslashes($_REQUEST['UserName']);
			
		$len_u = preg_replace("/&#([0-9]+);/", "-", $len_u );
						
		//-------------------------------------------------
		// Make sure the username and password were entered
		//-------------------------------------------------

		if ($_REQUEST['UserName'] == "") {
			$this->setLastErrorCode('no_username');
			return false;
		}
	  
		if ($_REQUEST['openid_url'] == "" && !$this->isReturn()) {
			$this->setLastErrorCode('openid_url_blank');
			return false;
		}

		//-------------------------------------------------
		// Check for input length
		//-------------------------------------------------

		if (strlen($len_u) > 32) {
			$this->setLastErrorCode('username_long');
			return false;
		}

		$this->setUsername($_REQUEST['UserName']);
		
		if ( !$this->isReturn() ) {
			$this->url = $_REQUEST['openid_url'];
			
			$member = $DB->get_row("SELECT id, name, mgroup, password, openid_url FROM ibf_members WHERE LOWER(name)='".$DB->quote($this->username())."'");
			
			if ($this->url != $member['openid_url']) {
				$this->setLastErrorCode('openid_url_forbidden');
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Это возврат от сервера OpenID
	 */
	private function isReturn() {
		return isset($_REQUEST['oidauth']) && ($_REQUEST['oidauth'] == 'continue');
	}
	
	public function authenticate() {
		global $DB, $ibforums, $std, $print, $sess;

		$username    = strtolower(str_replace( '|', '&#124;', $ibforums->input['UserName']) );

		//-------------------------------------------------
		// Attempt to get the user details
		//-------------------------------------------------

		$member = $DB->get_row("SELECT id, name, mgroup, password, openid_url FROM ibf_members WHERE LOWER(name)='".$DB->quote($username)."'");
		 
		if (!$member) {
			$this->setLastErrorCode('wrong_name');
			return false;
		}
		 
		if ( empty($member['id']) or ($member['id'] == "") ) {
			$this->setLastErrorCode('wrong_name');
			return false;
		}
		
		if ( !$this->isReturn() ) {
			$this->run();
		} else {
			if (!$this->finish()) {
				return false;
			}
			if ($this->url != $member['openid_url']) {
				$this->setLastErrorCode('openid_url_forbidden');
				return false;
			}
		}
		
		
		
		//------------------------------

		if ($ibforums->input['CookieDate']) {
			$std->my_setcookie("member_id"   , $member['id'], 1);
			$std->my_setcookie("openid_url"   , sha1($this->url), 1);
			$std->my_setcookie("auth_method"   , 'openid', 1);
		}

		return $member;
		 
	}
	
	public function sessionDataIsValid($member) {
		global $std;
		return ($std->my_getcookie('openid_url') == $member['openid_url']) || true;
	}
	
	function requireLib() {
		ini_set('include_path',dirname(__FILE__).'/../php-openid/' );
		/**
		 * Require the OpenID consumer code.
		 */
		require_once "Auth/OpenID/Consumer.php";

		/**
		 * Require the "file store" module, which we'll need to store
		 * OpenID information.
		 */
		require_once "Auth/OpenID/FileStore.php";

		/**
		 * Require the Simple Registration extension API.
		 */
		require_once "Auth/OpenID/SReg.php";

		/**
		 * Require the PAPE extension module.
		 */
		require_once "Auth/OpenID/PAPE.php";
		
		require_once 'Auth/OpenID.php';
	}

	private function getStore() {
		/**
		 * This is where the example will store its OpenID information.
		 * You should change this path if you want the example store to be
		 * created elsewhere.  After you're done playing with the example
		 * script, you'll have to remove this directory manually.
		 */
		$store_path = null;
		if (function_exists('sys_get_temp_dir')) {
			$store_path = sys_get_temp_dir();
		}
		else {
			if (strpos(PHP_OS, 'WIN') === 0) {
				$store_path = $_ENV['TMP'];
				if (!isset($store_path)) {
					$dir = 'C:\Windows\Temp';
				}
			}
			else {
				$store_path = @$_ENV['TMPDIR'];
				if (!isset($store_path)) {
					$store_path = '/tmp';
				}
			}
		}
		$store_path .= DIRECTORY_SEPARATOR . '_php_consumer_test';

		if (!file_exists($store_path) &&
		!mkdir($store_path)) {
			print "Could not create the FileStore directory '$store_path'. ".
            " Please check the effective permissions.";
			exit(0);
		}
		$r = new Auth_OpenID_FileStore($store_path);

		return $r;
	}

	private function getConsumer() {
		/**
		 * Create a consumer object using the store object created
		 * earlier.
		 */
		$store = $this->getStore();
		$r = new Auth_OpenID_Consumer($store);
		return $r;
	}
	
	private function getScheme() {
		$scheme = 'http';
		if (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on') {
			$scheme .= 's';
		}
		return $scheme;
	}

	private function getReturnTo() {
		global $ibforums;
		
		return Auth_OpenID::normalizeUrl("{$ibforums->base_url}act=Login&CODE=01&auth_method=openid&oidauth=continue&UserName=".$this->username());
	}

	private function getTrustRoot() {
		return sprintf("{$ibforums->base_url}",
			$this->getScheme(), $_SERVER['SERVER_NAME'],
			$_SERVER['SERVER_PORT'],
			dirname($_SERVER['PHP_SELF'])
		);
	}

	private function run() {
		$openid = $this->url;
		$consumer = $this->getConsumer();

		// Begin the OpenID authentication process.
		$auth_request = $consumer->begin($openid);

		// No auth request means we can't begin OpenID.
		if (!$auth_request) {
			displayError("Authentication error; not a valid OpenID.");
		}

		$sreg_request = Auth_OpenID_SRegRequest::build(
		// Required
		array('nickname'),
		// Optional
		array('fullname', 'email'));

		if ($sreg_request) {
			$auth_request->addExtension($sreg_request);
		}

		$policy_uris = null;
		if (isset($_GET['policies'])) {
			$policy_uris = $_GET['policies'];
		}

		$pape_request = new Auth_OpenID_PAPE_Request($policy_uris);
		if ($pape_request) {
			$auth_request->addExtension($pape_request);
		}

		// Redirect the user to the OpenID server for authentication.
		// Store the token for this authentication so we can verify the
		// response.

		// For OpenID 1, send a redirect.  For OpenID 2, use a Javascript
		// form to send a POST request to the server.
		if ($auth_request->shouldSendRedirect()) {
			$redirect_url = $auth_request->redirectURL(getTrustRoot(),
			getReturnTo());

			// If the redirect URL can't be built, display an error
			// message.
			if (Auth_OpenID::isFailure($redirect_url)) {
				displayError("Could not redirect to server: " . $redirect_url->message);
			} else {
				// Send redirect.
				header("Location: ".$redirect_url);
				exit;
			}
		} else {
			// Generate form markup and render it.
			$form_id = 'openid_message';
			// var_dump($this->getReturnTo());
			// die;
			$form_html = $auth_request->htmlMarkup($this->getTrustRoot(), $this->getReturnTo(),
			false, array('id' => $form_id));

			// Display an error if the form markup couldn't be generated;
			// otherwise, render the HTML.
			if (Auth_OpenID::isFailure($form_html)) {
				displayError("Could not redirect to server: " . $form_html->message);
			} else {
				print $form_html;
	    		exit;
			}
		}
	}
	
	function finish() {
		$consumer = $this->getConsumer();

		// Complete the authentication process using the server's
		// response.
		$return_to = $this->getReturnTo();
		$response = $consumer->complete($return_to);

		// Check the response status.
		if ($response->status == Auth_OpenID_CANCEL) {
			// This means the authentication was cancelled.
			$this->setLastErrorCode('openid_verification_cancelled');
			return false;
		} else if ($response->status == Auth_OpenID_FAILURE) {
			// Authentication failed; display the error message.
			//$msg = "OpenID authentication failed: " . $response->message;
			$this->setLastErrorCode('openid_authentication_failed');
			return false;
		} else if ($response->status == Auth_OpenID_SUCCESS) {
			// This means the authentication succeeded; extract the
			// identity URL and Simple Registration data (if it was
			// returned).
			$this->url = $response->getDisplayIdentifier();
			
			return true;
			/*
			$openid = $response->getDisplayIdentifier();
			$esc_identity = escape($openid);

			$success = sprintf('You have successfully verified ' .
                           '<a href="%s">%s</a> as your identity.',
			$esc_identity, $esc_identity);

			if ($response->endpoint->canonicalID) {
				$escaped_canonicalID = escape($response->endpoint->canonicalID);
				$success .= '  (XRI CanonicalID: '.$escaped_canonicalID.') ';
			}

			$sreg_resp = Auth_OpenID_SRegResponse::fromSuccessResponse($response);

			$sreg = $sreg_resp->contents();

			if (@$sreg['email']) {
				$success .= "  You also returned '".escape($sreg['email']).
                "' as your email.";
			}

			if (@$sreg['nickname']) {
				$success .= "  Your nickname is '".escape($sreg['nickname']).
                "'.";
			}

			if (@$sreg['fullname']) {
				$success .= "  Your fullname is '".escape($sreg['fullname']).
                "'.";
			}

			$pape_resp = Auth_OpenID_PAPE_Response::fromSuccessResponse($response);

			if ($pape_resp) {
				if ($pape_resp->auth_policies) {
					$success .= "<p>The following PAPE policies affected the authentication:</p><ul>";

					foreach ($pape_resp->auth_policies as $uri) {
						$escaped_uri = escape($uri);
						$success .= "<li><tt>$escaped_uri</tt></li>";
					}

					$success .= "</ul>";
				} else {
					$success .= "<p>No PAPE policies affected the authentication.</p>";
				}

				if ($pape_resp->auth_age) {
					$age = escape($pape_resp->auth_age);
					$success .= "<p>The authentication age returned by the " .
                    "server is: <tt>".$age."</tt></p>";
				}

				if ($pape_resp->nist_auth_level) {
					$auth_level = escape($pape_resp->nist_auth_level);
					$success .= "<p>The NIST auth level returned by the " .
                    "server is: <tt>".$auth_level."</tt></p>";
				}

			} else {
				$success .= "<p>No PAPE response was sent by the provider.</p>";
			}
			*/
		}
		return false;// unknown error
	}
}