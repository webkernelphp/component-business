<?php

declare(strict_types=1);

namespace Webkernel\Component\Business\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkernel\Component\Business\Enums\BusinessStatus;

/**
 * Business — the primary scoping unit in Webkernel.
 *
 * Represents a real-world entity (company, project, department, NGO, etc.).
 * Modules are activated against a Business. Permissions and roles are
 * scoped to (module, business).
 *
 * This is intentionally separate from any Filament "tenant" model.
 * The tenant model (Team, Organization, etc.) can have a belongsTo(Business).
 */
class Business extends Model
{
    protected $connection = 'webkernel_primary';

    protected $table = 'businesses';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'slug',
        'status',
        'admin_email',
        'created_by',
    ];

    protected $casts = [
        'status' => BusinessStatus::class,
    ];

    #[\Override]
    protected static function boot(): void
    {
        parent::boot();

        static::creating(static function (self $model): void {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = webkernel_id_generator()
                    ->makeUniqueIdentifier()
                    ->using('cuid2')
                    ->get();
            }

            if (empty($model->status)) {
                $model->status = BusinessStatus::PENDING;
            }

            if (empty($model->slug) && !empty($model->name)) {
                $model->slug = static::generateUniqueSlug($model->name);
            }
        });
    }

    // Relations

    public function creator(): BelongsTo
    {
        // Points to the application's User model
        return $this->belongsTo(config('auth.providers.users.model', \App\Models\User::class), 'created_by');
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('status', BusinessStatus::ACTIVE->value);
    }

    public function scopePending($query)
    {
        return $query->where('status', BusinessStatus::PENDING->value);
    }

    public function scopeForSlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    // Helpers

    public function isActive(): bool
    {
        return $this->status === BusinessStatus::ACTIVE;
    }

    public function activate(): bool
    {
        $this->status = BusinessStatus::ACTIVE;
        return $this->save();
    }

    public function suspend(): bool
    {
        $this->status = BusinessStatus::SUSPENDED;
        return $this->save();
    }

    public static function generateUniqueSlug(string $name): string
    {
        $base = strtolower(trim((string) preg_replace('/[^a-z0-9]+/i', '-', $name), '-'));
        $slug = $base;
        $n = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $n++;
        }

        return $slug;
    }
}
