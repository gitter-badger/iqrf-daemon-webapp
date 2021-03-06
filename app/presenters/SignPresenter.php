<?php

/**
 * Copyright 2017 MICRORISC s.r.o.
 * Copyright 2017 IQRF Tech s.r.o.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace App\Presenters;

use App\Forms\SignInFormFactory;
use Nette\Application\UI\Form;

/**
 * Sign in/out presenter
 */
class SignPresenter extends BasePresenter {

	/**
	 * @var SignInFormFactory
	 */
	private $signInFactory;

	/**
	 * Constructor
	 * @param SignInFormFactory $signInFactory
	 */
	public function __construct(SignInFormFactory $signInFactory) {
		$this->signInFactory = $signInFactory;
	}

	/**
	 * User sign in
	 */
	public function actionIn() {
		if ($this->user->isLoggedIn()) {
			$this->redirect('Homepage:');
		}
	}

	/**
	 * Create sign in form
	 * @return Form Sign in form
	 */
	protected function createComponentSignInForm() {
		return $this->signInFactory->create(function () {
					$this->redirect('Homepage:');
				});
	}

	/**
	 * User sign out
	 */
	public function actionOut() {
		if (!$this->user->isLoggedIn()) {
			$this->redirect('Sign:in');
		} else {
			$this->getUser()->logout();
		}
	}

}
