<?php
namespace App\Application\Handler;

use App\Application\Command\ChangeUserStateCommand;
use App\Domain\Repository\UserRepositoryInterface;
use App\Domain\Repository\StateRepositoryInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use App\Domain\Event\UserStateChanged;

class ChangeUserStateHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private StateRepositoryInterface $stateRepository,
        private EventDispatcherInterface $dispatcher
    ) {}

    public function __invoke(ChangeUserStateCommand $command): void
    {
        $user = $this->userRepository->findById($command->userId);
        if (!$user) {
            throw new \InvalidArgumentException("User not found");
        }

        $oldStateId = $user->state()->id();
        $newState = $this->stateRepository->findById($command->newStateId);
        if (!$newState) {
            throw new \InvalidArgumentException("State not found");
        }

        $user->changeState($newState);
        $this->userRepository->save($user);

        // Dispatch domain event
        $this->dispatcher->dispatch(new UserStateChanged($user, $oldStateId));
    }
}
