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
     * @param string|null $uuid
     * @return array
     */
    public function create(string $aud, string $uuid = null): array;

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