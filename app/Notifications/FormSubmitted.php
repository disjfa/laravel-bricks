<?php

namespace App\Notifications;

use App\Models\FormField;
use App\Models\FormSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * The email about a new submission of a form, with every answer. A reply goes to the visitor when the form
 * has an email field. Queued, so the visitor does not wait for the mail server.
 */
class FormSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public FormSubmission $submission) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $form = $this->submission->form;

        $message = (new MailMessage)
            ->subject(__('New submission: :form', ['form' => $form->name]))
            ->greeting(__('New submission'))
            ->line(__('Someone sent the form ":form" on the website.', ['form' => self::escapeMarkdown($form->name)]));

        foreach ($form->renderableFields() as $field) {
            $message->line('**'.self::escapeMarkdown($field->label()).':** '.self::escapeMarkdown($this->answer($field)));
        }

        if (($replyTo = $this->replyToAddress()) !== null) {
            $message->replyTo($replyTo);
        }

        return $message->action(__('View submissions'), route('admin.forms.submissions', $form));
    }

    protected function answer(FormField $field): string
    {
        $value = $this->submission->data[$field->name] ?? null;

        return match (true) {
            is_bool($value) => $value ? __('Yes') : __('No'),
            blank($value) => '-',
            default => (string) $value,
        };
    }

    /**
     * The answer of the first email field, when it is a valid address.
     */
    protected function replyToAddress(): ?string
    {
        foreach ($this->submission->form->renderableFields() as $field) {
            $value = $this->submission->data[$field->name] ?? null;

            if ($field->type === 'email' && is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL) !== false) {
                return $value;
            }
        }

        return null;
    }

    /**
     * The mail lines are Markdown: answers from visitors must not turn into links or formatting.
     */
    protected static function escapeMarkdown(string $text): string
    {
        return (string) preg_replace('/([\\\\`*_{}\[\]()#+\-.!|<>~])/', '\\\\$1', $text);
    }
}
