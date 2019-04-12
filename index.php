<?php

require __DIR__ . DS . "src" . DS . "Autopublish.php";

// For composer
@include_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin('bvdputte/kirbyAutopublish', [
    'options' => [
        'fieldName' => 'autopublish',
        'poormanscron' => false,
        'poormanscron.interval' => 1, // in minutes
        'cache.poormanscron' => true
    ],
    'collections' => [
        'autoPublishedDrafts' => function ($site) {
            $autopublishfield = option("bvdputte.kirbyAutopublish.fieldName");
            $drafts = $site->pages()->drafts();
            $autoPublishedDrafts = $drafts->filter(function ($draft) use ($autopublishfield) {
                return ($draft->$autopublishfield()->exists()) && (!$draft->$autopublishfield()->isEmpty());
            });

            return $autoPublishedDrafts;
        }
    ],
    'hooks' => [
        'route:before' => function ($route, $path, $method) {
            /*
             * For servers without cron, enable "poormanscron"
             * ⚠️ Ugly, non-performant hack to bypass cache
             * @TODO: Fix this as soon as this is possible: 
             * https://github.com/getkirby/ideas/issues/23
             */
            if (option("bvdputte.kirbyAutopublish.poormanscron")) {
                bvdputte\kirbyAutopublish\Autopublish::poorManCronRun();
            }
        }
    ]
]);