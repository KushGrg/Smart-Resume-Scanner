<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Crypt;

trait EncryptableFields
{
    /**
     * Fields that should be encrypted.
     */
    protected array $encryptable = [];

    /**
     * Boot the encryptable trait.
     */
    protected static function bootEncryptableFields(): void
    {
        static::saving(function ($model) {
            $model->encryptAttributes();
        });

        static::retrieved(function ($model) {
            $model->decryptAttributes();
        });
    }

    /**
     * Encrypt specified attributes.
     */
    protected function encryptAttributes(): void
    {
        foreach ($this->getEncryptableFields() as $field) {
            if (isset($this->attributes[$field]) &&
                ! empty($this->attributes[$field]) &&
                ! $this->isEncrypted($this->attributes[$field])) {

                $this->attributes[$field] = Crypt::encrypt($this->attributes[$field]);
            }
        }
    }

    /**
     * Decrypt specified attributes.
     */
    protected function decryptAttributes(): void
    {
        foreach ($this->getEncryptableFields() as $field) {
            if (isset($this->attributes[$field]) &&
                ! empty($this->attributes[$field]) &&
                $this->isEncrypted($this->attributes[$field])) {

                try {
                    $this->attributes[$field] = Crypt::decrypt($this->attributes[$field]);
                } catch (\Exception $e) {
                    // Handle decryption failure gracefully
                    logger()->warning("Failed to decrypt field {$field} for model ".get_class($this), [
                        'model_id' => $this->id ?? 'unknown',
                        'error' => $e->getMessage(),
                    ]);
                    $this->attributes[$field] = null;
                }
            }
        }
    }

    /**
     * Get the encryptable fields for this model.
     */
    protected function getEncryptableFields(): array
    {
        return $this->encryptable ?? [];
    }

    /**
     * Check if a value is already encrypted.
     */
    protected function isEncrypted(string $value): bool
    {
        // Laravel's encrypted values start with "eyJpdiI6" (base64 encoded JSON)
        return str_starts_with($value, 'eyJpdiI6');
    }

    /**
     * Set an encrypted attribute.
     */
    public function setEncryptedAttribute(string $key, $value): void
    {
        if (in_array($key, $this->getEncryptableFields()) && ! empty($value)) {
            $this->attributes[$key] = Crypt::encrypt($value);
        } else {
            $this->attributes[$key] = $value;
        }
    }

    /**
     * Get a decrypted attribute.
     */
    public function getDecryptedAttribute(string $key)
    {
        if (in_array($key, $this->getEncryptableFields()) &&
            isset($this->attributes[$key]) &&
            ! empty($this->attributes[$key])) {

            try {
                return Crypt::decrypt($this->attributes[$key]);
            } catch (\Exception $e) {
                logger()->warning("Failed to decrypt attribute {$key} for model ".get_class($this), [
                    'model_id' => $this->id ?? 'unknown',
                    'error' => $e->getMessage(),
                ]);

                return null;
            }
        }

        return $this->attributes[$key] ?? null;
    }

    /**
     * Search encrypted fields (Note: This is limited and should be used carefully).
     */
    public function scopeWhereEncrypted($query, string $field, string $value)
    {
        if (in_array($field, $this->getEncryptableFields())) {
            $encryptedValue = Crypt::encrypt($value);

            return $query->where($field, $encryptedValue);
        }

        return $query->where($field, $value);
    }
}
