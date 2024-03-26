<?php

namespace App\Controllers;

use \App\Models,
	\MvcCore\Ext\Form,
	\MvcCore\Ext\ModelForms\Form as ModelForm;

class CdCollection extends Base {

	/** @var Models\Album */
	protected $album;

	/**
	 * Initialize this controller, before pre-dispatching and before every action
	 * executing in this controller. This method is template method - so
	 * it's necessary to call parent method first.
	 * @return void
	 */
	public function Init () {
		parent::Init();
		// if user is not authorized, redirect to proper location and exit
		if (!$this->user) {
			// if post, get safe value from where the form has been submitted
			$sourceUrl = $this->request->GetFullUrl();
			if ($this->request->GetMethod() === \MvcCore\Request::METHOD_POST) {
				$referer = $this->request->GetReferer();
				$parsedUrlHost = \MvcCore\Tool::ParseUrl($referer, PHP_URL_HOST);
				if ($parsedUrlHost === $this->request->GetHostName())
					$sourceUrl = $referer;
			}
			self::Redirect($this->Url(
				'\Index:Index', ['sourceUrl' => rawurlencode($sourceUrl)]
			));
		}
	}

	/** @return void */
	public function EditInit () {
		$this->setUpAlbum();
	}

	/** @return void */
	public function DeleteInit () {
		$this->setUpAlbum();
	}
	
	/** @return void */
	public function SubmitInit () {
		$this->setUpAlbum();
	}
	
	/**
	 * If there is any 'id' param in `$_GET` or `$_POST`,
	 * try to load album model instance from database
	 * @return void
	 */
	protected function setUpAlbum () {
		$id = $this->GetParam('id', '0-9', NULL, 'int');
		if ($id !== NULL) 
			$this->album = Models\Album::GetById($id);
		if ($this->album === NULL) {
			$new = $this->GetParam('new', '0-1', FALSE, 'bool');
			if (!$new) 
				$this->RenderNotFound();
		}
	}

	/**
	 * Load all album items, create empty form  to delete any item
	 * to generate and manage CSRF tokens (once only, not
	 * for every album row) and add supporting js file
	 * to initialize javascript in delete post forms
	 * created multiple times in view only.
	 * @return void
	 */
	public function IndexAction () {
		$this->view->title = 'CD Collection';
		$this->view->albums = Models\Album::GetAll();
		$app = $this->application;
		if ($app->GetCsrfProtection() === $app::CSRF_PROTECTION_FORM_INPUT) {
			// old, but most compatible way
			list(
				$this->view->csrfName, $this->view->csrfValue
			) = $this->getVirtualDeleteForm()->SetUpCsrf();
		}
		$this->view->Js('varFoot')
			->Prepend(self::$staticPath . '/js/List.js');
	}

	/**
	 * Create form for new album without hidden id input.
	 * @return void
	 */
	public function CreateAction () {
		$this->view->title = 'New album';
		$form = $this->getCreateEditForm();
		if (!$form->GetErrors()) 
			$form->ClearSession();
		$this->view->detailForm = $form;
	}

	/**
	 * Load previously saved album data,
	 * create edit form with hidden id input
	 * and set form defaults with album values.
	 * @return void
	 */
	public function EditAction () {
		$this->view->title = 'Edit album - ' . $this->album->Title;
		$this->view->detailForm = $this->getCreateEditForm();
	}

	/**
	 * Handle create and edit action form submit.
	 * @return void
	 */
	public function SubmitAction () {
		$detailForm = $this->getCreateEditForm();
		list($result, $values, $errors) = $detailForm->Submit();
		if ($result != $detailForm::RESULT_ERRORS) {
			$this->album = $detailForm->GetModelInstance();
			if (($result & $detailForm::RESULT_SUCCESS_COPY) != 0) {
				$this->album
					->SetTitle($this->album->GetTitle() . ' - copy')
					->Save();
			}
		}
		$detailForm->SubmittedRedirect();
	}

	/**
	 * Delete album by sent id param, if sent CSRF tokens
	 * are the same as CSRF tokens in session (tokens are managed
	 * by empty virtual delete form initialized once, not for all album rows).
	 * @return void
	 */
	public function DeleteAction () {
		$app = $this->application;
		if ($app->GetCsrfProtection() === $app::CSRF_PROTECTION_FORM_INPUT) {
			$form = $this->getVirtualDeleteForm();
			$form->SubmitCsrfTokens($_POST); // old, but most compatible way
			if (!$form->GetErrors())
				$this->album->Delete();
		} else {
			if ($app->ValidateCsrfProtection()) 
				$this->album->Delete();
		}
		self::Redirect($this->Url(':Index'));
	}

	/**
	 * Create form instance to create new or edit existing album.
	 * @return ModelForm
	 */
	protected function getCreateEditForm () {
		$creating = $this->album === NULL;
		$form = new ModelForm($this);
		$form
			->SetModelClassFullName('App\\Models\\Album')
			->SetMethod(Form::METHOD_POST)
			->SetAction($this->Url(':Submit', ['new' => TRUE]))
			->SetSuccessUrl($this->Url(':Index'))
			->SetErrorUrl($this->Url($creating ? ':Create' : ':Edit'))
			->AddCssClasses(['detail', 'theme'])
			->SetFieldsRenderModeDefault(
				Form::FIELD_RENDER_MODE_LABEL_AROUND
			);
		if (!$creating)
			$form->SetModelInstance($this->album);
		// set up some form dynamic values:
		$form->Init();
		$yearField = $form->GetField('year');
		$yearField
			->SetMin(intval(date('Y')) - 500)
			->SetMax(date('Y'));
		return $form;
	}

	/**
	 * Create empty form, where to store and manage CSRF
	 * tokens for delete links in albums list.
	 * @return \MvcCore\Ext\Form|\MvcCore\Ext\Forms\IForm
	 */
	protected function getVirtualDeleteForm () {
		return (new Form($this))
			->SetId('delete')
			// set error url, where to redirect if CSRF
			// are wrong, see `\App\Controllers\Base::Init();`
			->SetErrorUrl(
				$this->Url('Index:Index', ['absolute' => TRUE])
			);
	}
}
