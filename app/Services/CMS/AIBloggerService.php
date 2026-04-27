<?php

namespace App\Services\CMS;

use App\Contracts\AiProviderContract;
use App\Jobs\GenerateArticleJob;
use App\Models\Tenant;

class AIBloggerService
{
    /**
     * Create a new service instance.
     *
     * @param AiProviderContract $aiProvider
     */
    public function __construct(
        protected AiProviderContract $aiProvider
    ) {}

    /**
     * Generate articles from a list of keyword clusters.
     *
     * @param Tenant $tenant
     * @param array $clusters
     * @return void
     */
    public function generateArticlesFromClusters(Tenant $tenant, array $clusters): void
    {
        foreach ($clusters as $cluster) {
            GenerateArticleJob::dispatch(
                $tenant, 
                $cluster, 
                $this->aiProvider->getName()
            );
        }
    }
}
