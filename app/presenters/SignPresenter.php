<?php
    namespace App\Presenters;

    use Nette;
    use Nette\Application\UI\Form;


    class SignPresenter extends Nette\Application\UI\Presenter
    {
        protected function createComponentSignInForm()
        {
            $form = new Form;
            $form->addText('username', 'Uživatelské jméno:')
                ->setRequired('Prosím vyplňte své uživatelské jméno.');

            $form->addPassword('password', 'Heslo:')
                ->setRequired('Prosím vyplňte své heslo.');

            $form->addSubmit('send', 'Přihlásit');

            $form->onSuccess[] = [$this, 'signInFormSucceeded'];
            return $form;
        }
        public function signInFormSucceeded(Form $form, \stdClass $values)
        {
            try {
                $this->getUser()->login($values->username, $values->password);
                $this->redirect('Homepage:');

            } catch (Nette\Security\AuthenticationException $e) {
                $form->addError('Nesprávné přihlašovací jméno nebo heslo.');
            }
        }
        public function actionOut()
        {
            $this->getUser()->logout();
            $this->flashMessage('Odlhášení bylo úspěšné');
            $this->redirect('Homepage');
        }
    }