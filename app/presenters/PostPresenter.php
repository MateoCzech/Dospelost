<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;

class PostPresenter extends Nette\Application\UI\Presenter
{
    /** @var Nette\Database\Context */
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        parent::__construct();
        $this->database = $database;
    }

    public function renderShow($postId)
    {
        $post = $this->database->table('posts')->get($postId);
        if (!$post) {
            try {
                $this->error('Stránka nebyla nalezena');
            } catch (Nette\Application\BadRequestException $e) {
            }
        }

        $this->template->post = $post;
        $this->template->post = $post;
        $this->template->comments = $post->related('comment')->order('created_at');
    }

    public function commentFormSucceeded(Form $form, \stdClass $values)
    {
        $postId = $this->getParameter('postId');

        $this->database->table('comments')->insert([
            'post_id' => $postId,
            'name' => $values->name,
            'email' => $values->email,
            'content' => $values->content,
        ]);

        $this->flashMessage('Děkuji za komentář', 'success');
        try {
            $this->redirect('this');
        } catch (Nette\Application\AbortException $e) {
        }
    }

    public function postFormSucceeded(Form $form, \stdClass $values)
    {
        if (!$this->getUser()->isLoggedIn()) {
            try {
                $this->error('Pro vytvoření, nebo editování příspěvku se musíte přihlásit.');
            } catch (Nette\Application\BadRequestException $e) {
            }
        }

        $postId = $this->getParameter('postId');

        if ($postId) {
            $post = $this->database->table('posts')->get($postId);
            $post->update($values);
        } else {
            $post = $this->database->table('posts')->insert($values);
        }

        $this->flashMessage('Příspěvek byl úspěšně publikován.', 'success');
        $this->redirect('show', $post->id);

    }

    public function actionEdit($postId)
    {

        $post = $this->database->table('posts')->get($postId);
        if (!$post) {
            try {
                $this->error('Příspěvek nebyl nalezen');
            } catch (Nette\Application\BadRequestException $e) {
            }
        }
        $this['newPostForm']->setDefaults($post->toArray());

        if(!$this->getUser()->isLoggedIn()){
            $this->redirect('Sign:in');
        }

    }

    public function actionCreate()
    {
        if(!$this->getUser()->isLoggedIn()){
            try {
                $this->redirect('Sign:in');
            } catch (Nette\Application\AbortException $e) {
            }
        }
    }

    protected function createComponentNewCreateForm()
    {

        $form = new Form; // means Nette\Application\UI\Form

        $form->addText('title', 'Název článku')
            ->setRequired();

        $form->addTextArea('content', 'Obsah')
            ->setRequired();

        $form->addSubmit('send', 'Publikovat článek');

        $form->onSuccess[] = [$this, 'postFormSucceeded'];

        return $form;
    }

    protected function createComponentCommentForm()
    {
        $form = new Form; // means Nette\Application\UI\Form

        $form->addText('name', 'Jméno:')
            ->setRequired();

        $form->addEmail('email', 'Email:');

        $form->addTextArea('content', 'Komentář:')
            ->setRequired();

        $form->addSubmit('send', 'Publikovat komentář');

        $form->onSuccess[] = [$this, 'commentFormSucceeded'];

        return $form;
    }

}
