<?php

namespace Base\Mail;

use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Message;
use Zend\View\Model\ViewModel;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;

class Mail
{
    protected $transport;
    protected $view;
    protected $body;
    protected $message;
    protected $subject;
    protected $to;
    protected $from;
    protected $data;
    protected $page;

    public function __construct(SmtpTransport $transport, $view, $page = null)
    {
        $this->transport = $transport;
        $this->view      = $view;
        $this->page      = $page;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    public function setTo($to)
    {
        $this->to = $to;
        return $this;
    }

    public function setFrom($from)
    {
        $this->from = $from;
        return $this;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function setPage($page)
    {
        $this->page = $page;
        return $this;
    }

    public function renverView($page, array $data)
    {
        $model = new ViewModel($data);
        $model->setTemplate("mailer/{$page}");
        $model->setOption('has_parent', true);

        return $this->view->render($model);
    }

    public function prepare($cc = array())
    {
        $html = new MimePart($this->renverView($this->page, $this->data));
        $html->type = "text/html";

        $body = new MimeMessage();
        $body->setParts(array($html));
        $this->body = $body;

        $this->message = new Message;
        $this->message->addFrom($this->from)
                ->addTo($this->to)
                ->setSubject($this->subject)
                ->setBody($this->body);

        foreach ($cc as $email) {
            $this->message->addCc($email);
        }

        return $this;
    }

    public function send()
    {
        $this->transport->send($this->message);
    }
}
