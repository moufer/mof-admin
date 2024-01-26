<?php

namespace mof\interface;

interface TokenInterface
{
    /**
     * @return string
     */
    public function uuid(): string;
    /**
     * @return void
     */
    public function destroy(): void;

    /**
     * @param string $aud
     * @return array
     */
    public function create(string $aud): array;

    /**
     * @param string $token
     * @return array
     */
    public function verify(string $token): array;

    /**
     * @return array
     */
    public function toArray(): array;

}