<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\UserRepositoryInterface;
use App\Interfaces\ClassRepositoryInterface;
use App\Interfaces\SubjectRepositoryInterface;
use App\Interfaces\ExamRepositoryInterface;
use App\Interfaces\QuestionRepositoryInterface;
use App\Interfaces\QuestionOptionRepositoryInterface;
use App\Interfaces\ExamAttemptRepositoryInterface;
use App\Interfaces\AIPromptRepositoryInterface;
use App\Interfaces\AIGenerationHistoryRepositoryInterface;
use App\Repositories\UserRepository;
use App\Repositories\ClassRepository;
use App\Repositories\SubjectRepository;
use App\Repositories\ExamRepository;
use App\Repositories\QuestionRepository;
use App\Repositories\QuestionOptionRepository;
use App\Repositories\ExamAttemptRepository;
use App\Repositories\AIPromptRepository;
use App\Repositories\AIGenerationHistoryRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ClassRepositoryInterface::class, ClassRepository::class);
        $this->app->bind(SubjectRepositoryInterface::class, SubjectRepository::class);
        $this->app->bind(ExamRepositoryInterface::class, ExamRepository::class);
        $this->app->bind(QuestionRepositoryInterface::class, QuestionRepository::class);
        $this->app->bind(QuestionOptionRepositoryInterface::class, QuestionOptionRepository::class);
        $this->app->bind(ExamAttemptRepositoryInterface::class, ExamAttemptRepository::class);
        $this->app->bind(AIPromptRepositoryInterface::class, AIPromptRepository::class);
        $this->app->bind(AIGenerationHistoryRepositoryInterface::class, AIGenerationHistoryRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
