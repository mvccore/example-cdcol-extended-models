<?php

namespace App\Controllers;

class Base extends \MvcCore\Controller
{
	/**
	 * Authenticated user instance is automatically assigned
	 * by authentication extension before `Controller::Init();`.
	 * @var \MvcCore\Ext\Auths\Basics\IUser
	 */
	protected $user = NULL;

	public function Init() {
		parent::Init();
		// when any CSRF token is outdated or not the same - sign out user by default
		$app = $this->application;
		if ($app->GetCsrfProtection() === $app::CSRF_PROTECTION_FORM_INPUT) {
			\MvcCore\Ext\Form::AddCsrfErrorHandler(function (\MvcCore\Ext\Form $form, $errorMsg) {
				$sourceUrl = $form->GetErrorUrl() !== NULL
					? $form->GetErrorUrl()
					: $form->GetSuccessUrl();
				$this->handleCsrfError($sourceUrl);
				return FALSE;
			});
		} else {
			$app->AddCsrfErrorHandler(function (\MvcCore\IRequest $req, \MvcCore\IResponse $res) {
				$this->handleCsrfError($this->Url('Index:Index'));
				return FALSE;
			});
		}
	}

	protected function handleCsrfError ($sourceUrl) {
		\MvcCore\Ext\Auths\Basics\User::LogOut();
		self::Redirect($this->Url(
			'Index:Index', [
				'absolute'	=> TRUE,
				'sourceUrl'	=> rawurlencode($sourceUrl ?: '')
			]
		));
	}

	public function PreDispatch () {
		parent::PreDispatch();
		if ($this->viewEnabled) {
			$this->_preDispatchSetUpAuth();
			$this->_preDispatchSetUpBundles();
			$this->view->basePath = $this->request->GetBasePath();
			$this->view->currentRouteCssClass = str_replace(
				':', '-', strtolower(
					$this->router->GetCurrentRoute()->GetName()
				)
			);
		}
	}

	private function _preDispatchSetUpAuth () {
		// init user in view
		$this->view->user = $this->user;
		if ($this->user) {
			// set sign-out form into view, set signed-out url to homepage:
			$this->view->signOutForm = \MvcCore\Ext\Auths\Basic::GetInstance()
				->GetSignOutForm()
				->SetValues([
					'successUrl' => $this->Url('Index:Index', ['absolute' => TRUE])
				]);
		}
	}

	private function _preDispatchSetUpBundles () {
		$cfgAssets = \MvcCore\Config::GetConfigSystem()->assets;
		\MvcCore\Ext\Views\Helpers\Assets::SetGlobalOptions((array) $cfgAssets);
		$static = self::$staticPath;
		$this->view->Css('fixedHead')
			->Append($static . '/css/components/resets.css')
			->Append($static . '/css/components/old-browsers-warning.css')
			->AppendRendered($static . '/css/components/fonts.css')
			->AppendRendered($static . '/css/components/forms-and-controls.css')
			->AppendRendered($static . '/css/components/content-buttons.css')
			->AppendRendered($static . '/css/components/content-tables.css')
			->AppendRendered($static . '/css/layout.css')
			->AppendRendered($static . '/css/content.css');
		$this->view->Js('fixedHead')
			->Append($static . '/js/libs/class.min.js')
			->Append($static . '/js/libs/ajax.min.js')
			->Append($static . '/js/libs/Module.js');
		$this->view->Js('varFoot')
			->Append($static . '/js/Front.js');
	}
}
