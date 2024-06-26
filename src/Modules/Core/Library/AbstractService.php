<?php
/*
 *     Copyright (c) 2024. Olayemi Faruq <olayemi@tonics.app>
 *
 *     This program is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU Affero General Public License as
 *     published by the Free Software Foundation, either version 3 of the
 *     License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU Affero General Public License for more details.
 *
 *     You should have received a copy of the GNU Affero General Public License
 *     along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Modules\Core\Library;

use App\Modules\Core\Validation\Traits\Validator;

abstract class AbstractService
{
    use Validator;

    private bool   $fails          = false;
    private string $message        = '';
    private mixed  $errors         = null;
    private string $redirectsRoute = '';

    public function fails (): bool
    {
        return $this->fails;
    }

    public function setFails (bool $fails): self
    {
        $this->fails = $fails;
        return $this;
    }

    public function getMessage (): string
    {
        return $this->message;
    }

    public function setMessage (string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function getRedirectsRoute (): string
    {
        return $this->redirectsRoute;
    }

    public function setRedirectsRoute (string $redirectsRoute): self
    {
        $this->redirectsRoute = $redirectsRoute;
        return $this;
    }

    /**
     * When there is fail, use this, if success, use message
     * @return mixed
     */
    public function getErrors (): mixed
    {
        return $this->errors;
    }

    public function setErrors (mixed $errors): self
    {
        if (is_string($errors)) {
            $errors = [$errors];
        }

        $this->errors = $errors;
        return $this;
    }

    /**
     * @return string
     */
    public function getErrorsAsString (): string
    {
        $error = '';
        $messages = iterator_to_array(new \RecursiveIteratorIterator(new \RecursiveArrayIterator($this->getErrors())), 0);
        foreach ($messages as $message) {
            $error .= $message . ', ';
        }

        return rtrim($error, ', ');
    }

}