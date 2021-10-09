<?php

namespace App\Core\Mail;

use App\Core\BaseController;
use App\Core\Database\QueryBuilder;

class MailController extends BaseController
{
    public function sendMail(Mail $mail): bool
    {
        $recipients = $this->prepareAddresses($mail->getRecipients());

        $headers = [
            'From' => $mail->getFrom(),
            'Reply-To' => $mail->getReplyTo(),
            'X-Mailer' => 'PHP/'.phpversion()
        ];

        if ($mail->getCc()) {
            $headers['Cc'] = $this->prepareAddresses($mail->getCc());
        }
        if ($mail->getBcc()) {
            $headers['Bcc'] = $this->prepareAddresses($mail->getBcc());
        }

        $mail->setSend(@mail($recipients, $mail->getSubject(), $mail->getContent(), $headers));
        $mail->setError(html_entity_decode(error_get_last()['message']));
        $this->saveMail($mail);

        return $mail->getSend();
    }


    private function prepareAddresses(array $addresses): string
    {
        return join(', ', $addresses);
    }


    private function saveMail(Mail $mail): void
    {
        $mailSaved = [
            'id_user' => $mail->getUser()->getId(),
            'subject' => $mail->getSubject(),
            'content' => $mail->getContent(),
            'send' => $mail->getSend(),
            'error' => $mail->getError()
        ];

        $q = new QueryBuilder();
        $q->insert('mails')->setValues($mailSaved)->execute();
    }
}
