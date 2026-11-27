<?php

namespace App\Models;

use App\Concerns\HasRevisions;
use App\Enums\UserRole;
use App\Http\Resources\Snapshots\FormSnapshot;
use App\Notifications\FormSubmitted;
use App\Revisions\Revisionable;
use Database\Factories\FormFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

/**
 * A form built in the form builder, placed on pages with the form brick.
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string|null $submit_label
 * @property string|null $success_message
 * @property string|null $notification_email
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, FormField> $fields
 * @property-read Collection<int, FormSubmission> $submissions
 */
#[Fillable(['name', 'description', 'submit_label', 'success_message', 'notification_email'])]
class Form extends Model implements Revisionable
{
    /** @use HasFactory<FormFactory> */
    use HasFactory;

    use HasRevisions;

    /**
     * @return HasMany<FormField, $this>
     */
    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('position');
    }

    /**
     * @return HasMany<FormSubmission, $this>
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function snapshotResource(): JsonResource
    {
        return new FormSnapshot($this);
    }

    /**
     * The submissions stay as they are: they keep the answers under the keys of the fields.
     */
    public function restoreSnapshot(array $snapshot): void
    {
        $this->update(collect($snapshot)->only(['name', 'description', 'submit_label', 'success_message', 'notification_email'])->all());

        $this->fields()->delete();

        foreach (array_values($snapshot['fields']) as $position => $field) {
            $this->fields()->create([
                'type' => $field['type'],
                'name' => $field['name'],
                'settings' => $field['settings'],
                'position' => $position,
            ]);
        }
    }

    /**
     * The addresses from the notification email setting, or none when it is empty.
     *
     * @return array<int, string>
     */
    public function notificationRecipients(): array
    {
        return collect(explode(',', (string) $this->notification_email))
            ->map(fn (string $address) => trim($address))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Email a new submission to the addresses of the form, or to the admins when it has none.
     */
    public function sendSubmissionNotification(FormSubmission $submission): void
    {
        $notification = new FormSubmitted($submission);
        $recipients = $this->notificationRecipients();

        if ($recipients === []) {
            // Not User::role(): that throws when the admin role was not seeded yet.
            $admins = User::query()->whereHas('roles', fn ($roles) => $roles->where('name', UserRole::Admin->value))->get();

            Notification::send($admins, $notification);

            return;
        }

        Notification::route('mail', $recipients)->notify($notification);
    }

    public function submitLabel(): string
    {
        return filled($this->submit_label) ? $this->submit_label : __('Send');
    }

    public function successMessage(): string
    {
        return filled($this->success_message) ? $this->success_message : __('Thank you, we received your message.');
    }

    /**
     * Validation rules for a submission, keyed by field name.
     *
     * @return array<string, array<int, mixed>>
     */
    public function submissionRules(): array
    {
        return $this->renderableFields()
            ->mapWithKeys(fn (FormField $field) => [$field->name => $field->rules()])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function submissionAttributes(): array
    {
        return $this->renderableFields()
            ->mapWithKeys(fn (FormField $field) => [$field->name => mb_strtolower($field->label())])
            ->all();
    }

    /**
     * The fields whose type still exists.
     *
     * @return Collection<int, FormField>
     */
    public function renderableFields(): Collection
    {
        return $this->fields
            ->filter(fn (FormField $field) => $field->definition() !== null)
            ->values();
    }
}
