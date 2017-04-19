<?php

set_include_path(Core::getRootPath() . '/phplib/third-party' . PATH_SEPARATOR . get_include_path());
require_once 'Auth/OpenID/Consumer.php';
require_once 'Auth/OpenID/FileStore.php';
require_once 'Auth/OpenID/SReg.php';
require_once 'Auth/OpenID/AX.php';

if (!isset($_SESSION)) {
  session_start();
}

class OpenID {

  private static function getStore() {
    $store_path = Config::get('global.tempDir') . '/dex_openidStorePath';
    if (!file_exists($store_path) && !mkdir($store_path)) {
      print "Could not create the FileStore directory '$store_path'";
      exit(0);
    }
    return new Auth_OpenID_FileStore($store_path);
  }

  private static function getConsumer() {
    return new Auth_OpenID_Consumer(self::getStore());
  }

  private static function getReturnTo() {
    return Request::getFullServerUrl() . "auth/revenireOpenid";
  }

  /**
   * Returns null and sets a flash message on all errors.
   **/
  static function beginAuth($openid, $policyUris) {
    $consumer = self::getConsumer();
    $auth_request = $consumer->begin($openid);

    if (!$auth_request) {
      FlashMessage::add('Ați introdus un OpenID incorect.');
      return null;
    }

    $sreg_request = Auth_OpenID_SRegRequest::build(array('nickname'), array('fullname', 'email'));
    if ($sreg_request) {
      $auth_request->addExtension($sreg_request);
    }

    $ax = new Auth_OpenID_AX_FetchRequest;
    $ax->add(Auth_OpenID_AX_AttrInfo::make('http://axschema.org/namePerson', 1, 1, 'fullname'));
    $ax->add(Auth_OpenID_AX_AttrInfo::make('http://axschema.org/contact/email', 1, 1, 'email'));
    $ax->add(Auth_OpenID_AX_AttrInfo::make('http://axschema.org/namePerson/first', 1, 1, 'firstname'));
    $ax->add(Auth_OpenID_AX_AttrInfo::make('http://axschema.org/namePerson/last', 1, 1, 'lastname'));
    $auth_request->addExtension($ax);

    // For OpenID 1, send a redirect.  For OpenID 2, use a Javascript form to send a POST request to the server.
    if ($auth_request->shouldSendRedirect()) {
      $redirect_url = $auth_request->redirectURL(Request::getFullServerUrl(), self::getReturnTo());

      if (Auth_OpenID::isFailure($redirect_url)) {
        FlashMessage::add('Nu vă putem redirecționa către serverul OpenID: ' . $redirect_url->message);
        return null;
      } else {
        header("Location: $redirect_url");
        SmartyWrap::display('auth/beginAuth.tpl');
        exit;
      }
    } else {
      $form_html = $auth_request->htmlMarkup(Request::getFullServerUrl(), self::getReturnTo(), false, array('id' => 'openid_message'));

      if (Auth_OpenID::isFailure($form_html)) {
        FlashMessage::add('Nu vă putem redirecționa către serverul OpenID: ' . $form_html->message);
        return null;
      } else {
        print $form_html;
      }
    }
  }

  /**
   * Returns null and sets a flash message on all errors.
   **/
  static function finishAuth() {
    $consumer = self::getConsumer();
    $return_to = self::getReturnTo();
    $response = $consumer->complete($return_to);

    if ($response->status == Auth_OpenID_CANCEL) {
      FlashMessage::add('Verificare anulată.');
      return null;
    } else if ($response->status == Auth_OpenID_FAILURE) {
      FlashMessage::add('Verificarea a eșuat: ' . $response->message);
      return null;
    } else if ($response->status == Auth_OpenID_SUCCESS) {
      $result = array('email' => '', 'nickname' => '', 'fullname' => '');
      $result['identity'] = htmlentities($response->getDisplayIdentifier());

      if ($response->endpoint->canonicalID) {
        $escaped_canonicalID = htmlentities($response->endpoint->canonicalID);
        // Ignored for now
      }

      $sreg_resp = Auth_OpenID_SRegResponse::fromSuccessResponse($response);
      if ($sreg_resp) {
        $sreg = $sreg_resp->contents();
        if (isset($sreg['email'])) {
          $result['email'] = $sreg['email'];
        }
        if (isset($sreg['nickname'])) {
          $result['nickname'] = $sreg['nickname'];
        }
        if (isset($sreg['fullname'])) {
          $result['fullname'] = $sreg['fullname'];
        }
      }

      $ax_resp = Auth_OpenID_AX_FetchResponse::fromSuccessResponse($response);
      if ($ax_resp) {
        $data = $ax_resp->data;
        if (isset($data['http://axschema.org/contact/email']) && count($data['http://axschema.org/contact/email'])) {
          $result['email'] = $data['http://axschema.org/contact/email'][0]; // Take this over sreg
        }
        if (isset($data['http://axschema.org/namePerson']) && count($data['http://axschema.org/namePerson'])) {
          $result['fullname'] = $data['http://axschema.org/namePerson'][0];
        }
        $names = array();
        if (isset($data['http://axschema.org/namePerson/first']) && count($data['http://axschema.org/namePerson/first'])) {
          $names[] = $data['http://axschema.org/namePerson/first'][0];
        }
        if (isset($data['http://axschema.org/namePerson/last']) && count($data['http://axschema.org/namePerson/last'])) {
          $names[] = $data['http://axschema.org/namePerson/last'][0];
        }
        if (count($names)) {
          $result['fullname'] = implode(' ', $names);
        }
      }

      return $result;
    }
  }
}

?>
