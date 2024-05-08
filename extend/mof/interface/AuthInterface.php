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
     * @param UserInterface $user
     * @return void
     */
    public function setUser(UserInterface $user): void;

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
     * @return bool
     */
    public function refresh(): bool;

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