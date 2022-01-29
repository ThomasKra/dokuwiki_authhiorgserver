<?php

/**
 * DokuWiki Plugin authhiorg (Auth Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  HiOrg Server GmbH <support@hiorg-server.de>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
  die();
}

if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once(DOKU_PLUGIN . 'action.php');

require('auth.php');

/**
 * All DokuWiki action plugins need to inherit from this class
 */
class action_plugin_authhiorg extends DokuWiki_Action_Plugin
{

  /*
     * plugin should use this method to register its handlers with the dokuwiki's event controller
     */
  function register(Doku_Event_Handler $controller)
  {
    $controller->register_hook('HTML_LOGINFORM_OUTPUT', 'BEFORE', $this, 'handleOldLoginForm');  // @deprecated
    $controller->register_hook('FORM_LOGIN_OUTPUT', 'BEFORE', $this, 'handleLoginForm');
    $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'handleDoLogin');
  }

  /**
   * When singleservice is wanted, do not show login, but execute login right away
   *
   * @param Doku_Event $event
   * @return bool
   */
  public function handleDoLogin(Doku_Event $event)
  {
    if ($event->data != 'login') return true;
    if (empty($_GET["token"])) {
      $url = auth_plugin_authhiorg::addUrlParams($this->getConf('ssourl'), array(
        "weiter" => auth_plugin_authhiorg::myUrl(array("do" => "login")), // do=login, damit wir für den 2. Schritt wieder hier landen
        "getuserinfo" => "name,vorname,username,email,user_id,gruppe"
      ));
      send_redirect($url);
    }
    return true; // never reached
  }

  function handleOldLoginForm(&$event, $param)
  {
    /** @var Doku_Form $form */
    $form = $event->data;

    $html = $this->prepareLoginButton();

    // remove login form if single service is set
    $form->_content = [];
    $form->_content[] = $html;
  }

  function prepareLoginButton()
  {
    $html = '';
    $html .= '<div>';
    $attr = buildAttributes([
      'href' => wl('start', array('do' => 'login'), true, '&'),
      'class' => 'plugin_authhiorg',
      'style' => 'background-color: red',
    ]);
    $html .= '<button ' . $attr . '><span>Login mit Hiorg</span></button> ';

    $html .= '</div>';
    return $html;
  }

  /**
   * Add the oAuth login links to login form
   *
   * @param Doku_Event $event event object by reference
   * @return void
   * @deprecated can be removed in the future
   */
  public function handleLoginForm(Doku_Event $event)
  {
    /** @var Form $form */
    $form = $event->data;
    $html = $this->prepareLoginButton();
    if (!$html) return;

    // remove login form if single service is set
    $singleService = $this->getConf('singleService');
    if ($singleService) {
      do {
        $form->removeElement(0);
      } while ($form->elementCount() > 0);
    }

    $form->addFieldsetOpen($this->getLang('loginwith'))->addClass('plugin_oauth');
    $form->addHTML($html);
    $form->addFieldsetClose();
  }
}
