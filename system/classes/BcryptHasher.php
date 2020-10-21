<?php

namespace Lampa;

class BcryptHasher
{
	protected $rounds = 10;
	
    public function make($value, array $options = []) {
        $hash = password_hash($value, PASSWORD_BCRYPT, [
            'cost' => $this->cost($options),
        ]);

        if ($hash === false) {
            throw new Exception('Bcrypt-хеширование не поддерживается.');
        }

        return $hash;
    }
	
    public function check($value, $hashedValue) {
        return password_verify($value, $hashedValue);
    }
	
    protected function cost(array $options = []) {
        return $options['rounds'] ?? $this->rounds;
    }

}