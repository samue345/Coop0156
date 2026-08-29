<?php

namespace App\Providers;

use App\Domain\CreditAnalysis\CreditEligibilityEvaluator;
use App\Domain\CreditAnalysis\Rules\IncomeCommitmentRule;
use App\Domain\CreditAnalysis\Rules\InstallmentRule;
use App\Domain\CreditAnalysis\Rules\InterestRateRule;
use App\Domain\CreditAnalysis\Rules\MinimumIncomeRule;
use App\Domain\CreditAnalysis\Rules\MinimumScoreRule;
use App\Repositories\CustomerRepositoryInterface;
use App\Repositories\EloquentCustomerRepository;
use App\Repositories\CreditAnalysisRepositoryInterface;
use App\Repositories\EloquentCreditAnalysisRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CreditEligibilityEvaluator::class, function ($app) {
            return new CreditEligibilityEvaluator([
                $app->make(MinimumIncomeRule::class),
                $app->make(MinimumScoreRule::class),
                $app->make(InterestRateRule::class),
                $app->make(InstallmentRule::class),
                $app->make(IncomeCommitmentRule::class),
            ]);
        });

        $this->app->bind(CustomerRepositoryInterface::class, EloquentCustomerRepository::class);
        $this->app->bind(CreditAnalysisRepositoryInterface::class, EloquentCreditAnalysisRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
