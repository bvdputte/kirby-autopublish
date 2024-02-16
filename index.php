<?php

require __DIR__ . "/src/Autopublish.php";

// For composer
@include_once __DIR__ . '/vendor/autoload.php';

Kirby::plugin('bvdputte/autopublish', [
    'options' => [
        'fieldName' => 'autopublish',
        'fieldNameUnpublish' => 'autounpublish',
        'poormanscron' => false,
        'poormanscron.interval' => 1, // in minutes
        'cache.poormanscron' => true,
        'webhookToken' => false,
    ],
    'collections' => [
        'autoPublishedDrafts' => function ($site) {
            $autopublishfield = option("bvdputte.autopublish.fieldName");
            $drafts = $site->index()->drafts();
            $drafts->add($site->drafts()); //adds top level drafts to collection
            $autoPublishedDrafts = $drafts->filter(function ($draft) use ($autopublishfield) {
                return ($draft->$autopublishfield()->exists()) && ($draft->$autopublishfield()->isNotEmpty()) && (empty($draft->errors()) === true);
            });
            return $autoPublishedDrafts;
        },
        'autoUnpublishedListed' => function ($site) {
            $autounpublishfield = option("bvdputte.autopublish.fieldNameUnpublish");
            $listed = $site->index()->children()->listed();
            $listed->add($site->children()->listed()); //adds top level listed pages to collection
            $autoUnpublishedListed = $listed->filter(function ($listedPage) use ($autounpublishfield) {
                return ($listedPage->$autounpublishfield()->exists()) && ($listedPage->$autounpublishfield()->isNotEmpty()) && (empty($listedPage->errors()) === true);
            });
            return $autoUnpublishedListed;
        }
    ],
    'hooks' => [
        'route:before' => function ($route, $path, $method) {
            /*
             * For servers without cron, enable "poormanscron"
             * ⚠️ Ugly, non-performant hack to bypass cache
             */
            if (option("bvdputte.autopublish.poormanscron")) {
                bvdputte\kirbyAutopublish\Autopublish::poorManCronRun();
            }
        }
    ],
    'routes' => [
        [
            'pattern' => 'kirby-autopublish/(:any)',
            'action' => function ($token) {
                if (
                    $token !== option('bvdputte.autopublish.webhookToken', false) ||
                    option('bvdputte.autopublish.webhookToken', false) === false
                ) {
                    throw new Exception('Invalid token');
                    return false;
                }

                bvdputte\kirbyAutopublish\Autopublish::publish();
                bvdputte\kirbyAutopublish\Autopublish::unpublish();
                return new Response('done', 'text/html');
            }
        ],
    ]
]);
