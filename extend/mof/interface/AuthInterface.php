<?php

namespace mof\interface;

interface AuthInterface
{
    /**
     * @return int
     */
    public function getId(): int;

    /**
     * @return UserInterface
     */
    public function getUser(): UserInterface;

    /**
     * @return TokenInterface
     */
    public function getToken(): TokenInterface;

    /**
     * @param UserInterface $user
     * @return bool
     */
    public function login(UserInterface $user): bool;

    /**
     * @return bool
     */
    public function logout(): bool;

    /**
     * @return void
     */
    public function refresh(): void;

    /**
     * @param string $token
     * @return bool
     */
    public function verify(string $token): bool;

    /**
     * @return bool
     */
    public function isLogin(): bool;
}