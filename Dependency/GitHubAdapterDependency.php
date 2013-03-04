<?php

namespace Dependency;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

use App;

class GitHubAdapterDependency
{
    public function load(&$container)
    {
        $container->setParameter("gitHubApi", "Library\\GitHub\\GitHubApi");
        $container->register("gitHubAdapter", "Library\\GitHub\\GitHubAdapter")
            ->addArgument("%gitHubApi%");
    }
}